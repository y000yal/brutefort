import React, {useState, forwardRef, useImperativeHandle, useEffect} from "react";
import {Input, CheckBox, Tooltip} from "../../components/forms";
import {Info} from "@phosphor-icons/react";
import {RateLimitProps} from "../../types";
import api from "../../axios/api";
import Spinner from "../../components/Spinner";

const RateLimit = forwardRef((props: RateLimitProps, ref: React.Ref<any>) => {
    const [enableLockoutExtension, setEnableLockoutExtension] = useState(false);
    const {errors, settings} = props;
    const [isLoading, setIsLoading] = useState(false);
    const [initialFormData, setInitialFormData] = useState({});
    useImperativeHandle(ref, () => ({
        /**
         * Retrieves form data from the DOM elements related to rate limiting settings.
         *
         * @returns {Object} An object containing the values and types of various
         * rate limiting settings:
         */
        getFormData: () => {
            return {
                bf_max_attempts: {
                    value: (document.getElementById('bf-max-attempts') as HTMLInputElement | null)?.value || '',
                    type: (document.getElementById('bf-max-attempts') as HTMLInputElement | null)?.type || '',
                    required: true,
                },
                bf_time_window: {
                    value: (document.getElementById('bf-time-window') as HTMLInputElement | null)?.value || '',
                    type: (document.getElementById('bf-time-window') as HTMLInputElement | null)?.type || '',
                    required: true,
                },
                bf_lockout_duration: {
                    value: (document.getElementById('bf-lockout-duration') as HTMLInputElement | null)?.value || '',
                    type: (document.getElementById('bf-lockout-duration') as HTMLInputElement | null)?.type || '',
                    required: true,
                },
                bf_enable_lockout_extension: {
                    value: (document.getElementById('bf-enable-lockout-extension') as HTMLInputElement | null)?.checked || false,
                    type: (document.getElementById('bf-enable-lockout-extension') as HTMLInputElement | null)?.type || '',
                    required: false,
                },
                bf_extend_duration: {
                    value: (document.getElementById('bf-extend-duration') as HTMLInputElement | null)?.value || '',
                    type: (document.getElementById('bf-extend-duration') as HTMLInputElement | null)?.type || '',
                    required: true,
                },
                bf_custom_error_message: {
                    value: (document.getElementById('bf-custom-error-message') as HTMLInputElement | null)?.value || '',
                    type: (document.getElementById('bf-custom-error-message') as HTMLInputElement | null)?.type || '',
                    required: true
                },
            };
        }
    }));

    const handleLockoutExtension = (e: React.ChangeEvent<HTMLInputElement>) => {
        setEnableLockoutExtension(e.target.checked);
    };

    useEffect(() => {
        setIsLoading(true);
        const routeConfig = settings?.Routes?.Index?.value;
        const endpoint = settings?.id;
        const url = `${endpoint}${routeConfig}`;
        api.get(url).then(result => {
            if (result.status === 200) {
                setIsLoading(false);
                setInitialFormData(result?.data?.data);
            }
        })
    }, [])
    useEffect(() => {
        setEnableLockoutExtension(initialFormData?.bf_enable_lockout_extension);
    }, [initialFormData])
    return (
        <div className="flex gap-4 justify-around flex-col">
            {isLoading || !Object.keys(initialFormData).length ? (
                <div className="flex items-center justify-center">
                    <Spinner size={18} className="rounded-lg" color="border-primary-light" />
                </div>
            ) : (
                <>
                    <div className="flex gap-3 flex-col">
                        <div className="settings-title flex items-center gap-1">
                            <span
                                className="font-medium text-sm text-gray-700 dark:text-white">Max Allowed Attempts</span>
                            <Tooltip content="e.g. default 3 attempts per 15 minutes">
                                <Info/>
                            </Tooltip>
                        </div>
                        <div className="settings-body flex items-center gap-2">
                            <Input
                                id="bf-max-attempts"
                                name="bf_max_attempts"
                                min={1}
                                defaultValue={initialFormData?.bf_max_attempts || 3}
                                type="number"
                                className={`w-[50px] ${errors?.bf_max_attempts ? 'input-error' : ''}`}
                            />
                            <span className="italic">attempt(s)/</span>
                            <Input
                                id="bf-time-window"
                                name="bf_time_window"
                                min={1}
                                defaultValue={initialFormData?.bf_time_window || 15}
                                type="number"
                                placeholder="in minutes..."
                                className={`w-[80px] ${errors?.bf_time_window ? 'input-error' : ''}`}
                            />
                            <span className="italic">minutes</span>
                        </div>
                    </div>
                    <>
                        <Input
                            id="bf-lockout-duration"
                            name="bf_lockout_duration"
                            min={1}
                            defaultValue={initialFormData?.bf_lockout_duration || 5}
                            type="number"
                            label="Lockout Duration"
                            placeholder="in minutes..."
                            tooltip="How long an IP is blocked after limit."
                            className={errors?.bf_lockout_duration ? 'input-error' : ''}

                        />
                    </>
                    <>
                        <CheckBox
                            id="bf-enable-lockout-extension"
                            name="bf_enable_lockout_extension"
                            defaultChecked={initialFormData?.bf_enable_lockout_extension || enableLockoutExtension}
                            label="Enable lockout extension"
                            onChange={handleLockoutExtension}
                            tooltip="Tick this if you want to extend the lockout period if the login attempt keeps on failing (in hrs)."
                            className={errors?.bg_extend_lockout ? 'input-error' : ''}
                        />
                    </>
                    <>
                        {enableLockoutExtension && (
                            <Input
                                id="bf-extend-duration"
                                name="bf_extend_duration"
                                min={1}
                                defaultValue={initialFormData?.bf_extend_duration || 1}
                                type="number"
                                label="Extended Duration"
                                placeholder="in hours..."
                                tooltip="How long should the restriction time be extended in hours."
                                className={errors?.bf_extend_duration ? 'input-error' : ''}
                            />
                        )}
                    </>
                    <>
                        <Input
                            className={errors?.bf_custom_error_message ? 'input-error' : ''}
                            id="bf-custom-error-message"
                            name="bf_custom_error_message"
                            defaultValue={initialFormData?.bf_custom_error_message || "Too many attempts!!"}
                            type="text"
                            label="Custom Error Message"
                            placeholder="Too many attempts!!."
                            tooltip="Change the error message shown when limit is reached."
                        />
                    </>
                </>
            )}

        </div>
    );
});

export default RateLimit;
