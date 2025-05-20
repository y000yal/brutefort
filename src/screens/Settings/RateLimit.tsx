import React, {useState, forwardRef, useImperativeHandle, useEffect, useRef} from "react";
import {Input, CheckBox, Tooltip} from "../../components/forms";
import {Info} from "@phosphor-icons/react";
import {RateLimitProps} from "../../types";
import api from "../../axios/api";
import Spinner from "../../components/Spinner";
import { useQuery, useQueryClient } from '@tanstack/react-query';

const RateLimit = forwardRef((props: RateLimitProps, ref: React.Ref<any>) => {
    const [enableLockoutExtension, setEnableLockoutExtension] = useState(false);
    const [enableLockout, setEnableLockout] = useState(true);
    const {errors, settings} = props;
    const [isFetching, setIsFetching] = useState(false);
    const [initialFormData, setInitialFormData] = useState({});
    const maxAttemptsRef = useRef<HTMLInputElement>(null);
    const timeWindowRef = useRef<HTMLInputElement>(null);
    const enableLockoutRef = useRef<HTMLInputElement>(null);
    const lockoutDurationRef = useRef<HTMLInputElement>(null);
    const enableLockoutExtensionRef = useRef<HTMLInputElement>(null);
    const extendLockoutDurationRef = useRef<HTMLInputElement>(null);
    const customErrorMessageRef = useRef<HTMLInputElement>(null);

    useImperativeHandle(ref, () => ({
        /**
         * Retrieves form data from the DOM elements related to rate limiting settings.
         *
         * @returns {Object} An object containing the values and types of various
         * rate limiting settings:
         */
        getFormData: () => ({
            bf_max_attempts: {
                value: maxAttemptsRef.current?.value || '',
                type: maxAttemptsRef.current?.type || '',
                required: true,
            },
            bf_time_window: {
                value: timeWindowRef.current?.value || '',
                type: timeWindowRef.current?.type || '',
                required: true,
            },
            bf_enable_lockout: {
                value: enableLockoutRef.current?.checked || false,
                type: enableLockoutRef.current?.type || '',
                required: true,
            },
            bf_lockout_duration: {
                value: lockoutDurationRef.current?.value || '',
                type: lockoutDurationRef.current?.type || '',
                required: true,
            },
            bf_enable_lockout_extension: {
                value: enableLockoutExtensionRef.current?.checked || false,
                type: enableLockoutExtensionRef.current?.type || '',
                required: false,
            },
            bf_extend_lockout_duration: {
                value: extendLockoutDurationRef.current?.value || '',
                type: extendLockoutDurationRef.current?.type || '',
                required: true,
            },
            bf_custom_error_message: {
                value: customErrorMessageRef.current?.value || '',
                type: customErrorMessageRef.current?.type || '',
                required: true,
            },
        })
    }));

    const handleLockoutExtension = (e: React.ChangeEvent<HTMLInputElement>) => {
        setEnableLockoutExtension(e.target.checked);
    };
    const handleLockout = (e: React.ChangeEvent<HTMLInputElement>) => {
        setEnableLockout(e.target.checked);
    };
    /**
     * fetch settings data
     */
    useEffect(() => {
        setIsFetching(true);
        const routeConfig = settings?.Routes?.Index?.value;
        const endpoint = settings?.id;
        const url = `${endpoint}${routeConfig}`;
        api.get(url).then(result => {
            if (result.status === 200) {
                setIsFetching(false);
                const data = result?.data?.data;
                setInitialFormData(data !== null ? data : initialFormData);
            }
        })
    }, [])
    useEffect(() => {
        setEnableLockoutExtension(initialFormData?.bf_enable_lockout_extension);
        setEnableLockout(initialFormData?.bf_enable_lockout);
    }, [initialFormData])

    return (
        <div className="flex gap-4 justify-around flex-col">
            {isFetching || !Object.keys(initialFormData).length ? (
                <div className="flex items-center justify-center">
                    <Spinner size={18} className="rounded-lg" color="border-primary-light"/>
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
                                ref={maxAttemptsRef}
                                id="bf-max-attempts"
                                name="bf_max_attempts"
                                min={1}
                                defaultValue={initialFormData?.bf_max_attempts || 3}
                                type="number"
                                className={`w-[50px] ${errors?.bf_max_attempts ? 'input-error' : ''}`}
                            />
                            <span className="italic">attempt(s)/</span>
                            <Input
                                ref={timeWindowRef}
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
                        <CheckBox
                            ref={enableLockoutRef}
                            id="bf-enable-lockout"
                            name="bf_enable_lockout"
                            defaultChecked={initialFormData?.bf_enable_lockout || enableLockout}
                            label="Enable lockout"
                            onChange={handleLockout}
                            tooltip="Enabling this will override requests per set time just above."
                            className={errors?.bf_enable_lockout ? 'input-error' : ''}
                        />
                    </>
                    {enableLockout && (
                        <>
                            <>
                                <Input
                                    ref={lockoutDurationRef}
                                    id="bf-lockout-duration"
                                    name="bf_lockout_duration"
                                    min={1}
                                    defaultValue={initialFormData?.bf_lockout_duration || 5}
                                    type="text"
                                    label="Lockout Duration"
                                    placeholder="in minutes..."
                                    tooltip="How long an IP is blocked after limit (in minutes)."
                                    className={`html-duration-picker ${errors?.bf_lockout_duration ? 'input-error' : ''}`}

                                />
                            </>
                            <>
                                <CheckBox
                                    ref={enableLockoutExtensionRef}
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
                                        ref={extendLockoutDurationRef}
                                        id="bf-extend-lockout-duration"
                                        name="bf_extend_lockout_duration"
                                        min={1}
                                        defaultValue={initialFormData?.bf_extend_lockout_duration || 1}
                                        type="number"
                                        label="Extended Duration"
                                        placeholder="in hours..."
                                        tooltip="How long should the restriction time be extended in hours."
                                        className={errors?.bf_extend_lockout_duration ? 'input-error' : ''}
                                    />
                                )}
                            </>
                        </>
                    )}

                    <>
                        <Input
                            ref={customErrorMessageRef}
                            className={errors?.bf_custom_error_message ? 'input-error' : ''}
                            id="bf-custom-error-message"
                            name="bf_custom_error_message"
                            defaultValue={initialFormData?.bf_custom_error_message || "Too many attempts!! Try again after {{locked_out_until}}."}
                            type="text"
                            label="Custom Error Message"
                            placeholder="Too many attempts!! Try again after {{locked_out_until}}."
                            tooltip="Use {{locked_out_until}} tag to show locked out until period."
                        />
                    </>
                </>
            )}

        </div>
    );
});

export default RateLimit;
