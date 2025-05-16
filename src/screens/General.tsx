/** global BruteFortData **/
import React, {useRef, useState} from "react";
import {SETTINGS} from "../constants/settings";
import {__} from "@wordpress/i18n";
import Spinner from "../components/Spinner";
import {showToast} from "../utils";
import api from "../axios/api";

const GeneralTab = () => {
    const [activeSetting, setActiveSetting] = useState('rateLimitSettings');
    const [isSaving, setIsSaving] = useState(false);
    const formRef = useRef(null);

    const ActiveComponent = SETTINGS[activeSetting].component;

    const [errors, setErrors] = useState({});

    const handleSave = (): any => {
        setIsSaving(true);
        const routeConfig = SETTINGS?.[activeSetting]?.Routes?.Save;
        const endpoint = SETTINGS?.[activeSetting]?.id;

        if (!BruteFortData?.restUrl || !endpoint || !routeConfig?.value) {
            showToast(__("Missing Api Config!!", 'brutefort'), {type: 'error'});
        }

        const formData = formRef.current?.getFormData?.() || {};

        api
            .post(`${endpoint}${routeConfig.value}`, {
                formData
            })
            .then(response => {
                if (response.status == 200) {
                    showToast(response?.message || __("Settings saved successfully.", "brutefort"), {type: "success"})
                    setErrors({})
                }
            })
            .catch(response => {

               if(response.status > 200) {
                   showToast(response?.response?.data?.message || __("Settings not saved.", "brutefort"), {type: "error"})
                   setErrors(response?.response?.data?.errors || errors)
               }
            })
            .finally(() => setIsSaving(false))
    };

    return (
        <div className="p-4 rounded-lg w-full items-center justify-center transition-colors flex duration-300 gap-4">
            <div className="min-w-xl max-w-80">
                <div className="header flex items-center justify-between mb-5 rounded-md">
                    <div>
                        <span className="text-2xl font-bold">{__(SETTINGS[activeSetting].label, 'brutefort')}</span>
                        <p style={{marginTop: '5px'}}>{__(SETTINGS[activeSetting].description, 'brutefort')}</p>
                        <div className="flex h-[50px] gap-3 overflow-x-scroll scrollbar scrollbar-thin">
                            {Object.entries(SETTINGS).map(([key, {label, icon: Icon}]) => (
                                <button
                                    key={key}
                                    className={`min-w-fit max-h-[30px] rounded-lg flex items-start justify-center gap-2 pt-1 pb-1 pr-2 pl-2 cursor-pointer ${activeSetting === key ? 'text-gray-600 bg-[#f3f4f7]' : ''}`}
                                    onClick={() => setActiveSetting(key)}
                                >
                                    <Icon size={24}/>
                                    <span>{__(label, 'brutefort')}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                    <div className="save-btn flex gap-2 items-center">
                        <button className="button button-primary" onClick={handleSave}>
                            {__('Save', 'brutefort')}
                        </button>
                        {isSaving && <Spinner size={18} className="rounded-lg" color="border-primary-light"/>}
                    </div>
                </div>
                <hr/>
                <div className="settings-body flex flex-col mt-5">
                    <ActiveComponent ref={formRef} errors={errors} settings={SETTINGS[activeSetting]}/>
                </div>
            </div>
        </div>
    );
};

export default GeneralTab;
