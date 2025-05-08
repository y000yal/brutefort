import React, {useState} from "react";
import {SETTINGS} from "../constants/settings";

const GeneralTab = () => {
    const [activeSetting, setActiveSetting] = useState('rateLimitSettings');
    const ActiveComponent = SETTINGS[activeSetting].component;

    return (
        <div
            className=" p-4 rounded-lg w-full items-center justify-center transition-colors flex  duration-300 gap-4 ">
            <div className="min-w-xl max-w-80">
                <div className="header flex items-center justify-between mb-5 rounded-md">
                    <div>
                        <span className="text-2xl font-bold">Settings</span>
                        <p style={{marginTop: '5px'}}>All your general settings belongs in this area.</p>
                        <div className="flex h-[50px] gap-3 overflow-x-scroll scrollbar  scrollbar-thin">
                            {Object.entries(SETTINGS).map(([key, {label, icon: Icon}]) => (
                                <button
                                    key={key}
                                    className={`min-w-fit max-h-[30px] rounded-lg flex items-start justify-center gap-2  pt-1 pb-1 pr-2 pl-2 cursor-pointer ${
                                        activeSetting === key
                                            ? 'text-gray-600 bg-[#f3f4f7]'
                                            : ''
                                    }`}
                                    onClick={() => setActiveSetting(key)}
                                >
                                    <Icon size={24}/>
                                    <span className="">{label}</span>

                                </button>
                            ))}
                        </div>
                    </div>
                    <div className="save-btn">
                        <button className="button button-primary">
                            Save
                        </button>
                    </div>

                </div>
                <hr/>
                <div className="settings-body flex flex-col mt-5">
                    <ActiveComponent />
                </div>
            </div>
        </div>
    );
};

export default GeneralTab;

