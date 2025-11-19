import React, { useState, forwardRef, useImperativeHandle, useEffect, useRef } from "react";
import { Input, CheckBox } from "../../components/forms";
import { Gauge } from "@phosphor-icons/react";
import { RateLimitProps } from "../../types";
import api from "../../axios/api";
import Spinner from "../../components/Spinner";
import { useQuery } from '@tanstack/react-query';
import { __ } from "@wordpress/i18n";

const RateLimit = forwardRef((props: RateLimitProps, ref: React.Ref<any>) => {
    const [enableLockoutExtension, setEnableLockoutExtension] = useState(false);
    const [enableLockout, setEnableLockout] = useState(true);
    const { errors, settings } = props;
    const [isFetching, setIsFetching] = useState(false);
    const [initialFormData, setInitialFormData] = useState({});
    const maxAttemptsRef = useRef<HTMLInputElement>(null);
    const timeWindowRef = useRef<HTMLInputElement>(null);
    const enableLockoutRef = useRef<HTMLInputElement>(null);
    const lockoutDurationRef = useRef<HTMLInputElement>(null);
    const enableLockoutExtensionRef = useRef<HTMLInputElement>(null);
    const extendLockoutDurationRef = useRef<HTMLInputElement>(null);
    const customErrorMessageRef = useRef<HTMLInputElement>(null);

    const indexRoute = settings?.Routes?.Index?.value;
    const endpoint = settings?.id;
    const url = `${endpoint}${indexRoute}`;

    useImperativeHandle(ref, () => ({
        getFormData: () => ({
            brutef_max_attempts: {
                value: maxAttemptsRef.current?.value || '',
                type: maxAttemptsRef.current?.type || '',
                required: true,
            },
            brutef_time_window: {
                value: timeWindowRef.current?.value || '',
                type: timeWindowRef.current?.type || '',
                required: true,
            },
            brutef_enable_lockout: {
                value: enableLockoutRef.current?.checked || false,
                type: enableLockoutRef.current?.type || '',
                required: true,
            },
            brutef_lockout_duration: {
                value: lockoutDurationRef.current?.value || '',
                type: lockoutDurationRef.current?.type || '',
                required: true,
            },
            brutef_enable_lockout_extension: {
                value: enableLockoutExtensionRef.current?.checked || false,
                type: enableLockoutExtensionRef.current?.type || '',
                required: false,
            },
            brutef_extend_lockout_duration: {
                value: extendLockoutDurationRef.current?.value || '',
                type: extendLockoutDurationRef.current?.type || '',
                required: true,
            },
            brutef_custom_error_message: {
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

    const { data, isLoading } = useQuery({
        initialData: undefined,
        queryKey: ['rate-limit-settings-index'],
        queryFn: async () => {
            const res = await api.get(url);
            return res.data.data;
        },
        staleTime: Infinity,
        enabled: !!url
    });

    useEffect(() => {
        if (data) {
            setInitialFormData(data);
            setEnableLockoutExtension(data?.brutef_enable_lockout_extension);
            setEnableLockout(data?.brutef_enable_lockout);
        }
    }, [data]);

    return (
        <div className="flex flex-col gap-5">
            {isFetching || !Object.keys(initialFormData).length ? (
                <div className="flex justify-center items-center p-10">
                    <Spinner size={30} color="border-blue-500" />
                </div>
            ) : (
                <>
                    {/* General Rate Limits Card */}
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div className="mb-6">
                            <div className="flex items-center gap-2 mb-3">
                                <div className="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded text-blue-600 dark:text-blue-400">
                                    <Gauge size={18} weight="bold" />
                                </div>
                                <span className="text-base font-semibold text-gray-900 dark:text-white">
                                    {__("General Rate Limits", "brutefort")}
                                </span>
                            </div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {__("Control how many login attempts are allowed.", "brutefort")}
                            </p>
                        </div>

                        <div className="space-y-4">
                            <Input
                                label="Max Allowed Attempts"
                                ref={maxAttemptsRef}
                                id="bf-max-attempts"
                                name="brutef_max_attempts"
                                min={1}
                                defaultValue={initialFormData?.brutef_max_attempts || 3}
                                type="number"
                                className={`${errors?.brutef_max_attempts ? 'input-error' : ''}`}
                                tooltip="e.g. default 3 attempts per 15 minutes"
                            />
                            <div className="flex gap-2 items-end">
                                <Input
                                    label="Time Period"
                                    ref={timeWindowRef}
                                    id="bf-time-window"
                                    name="brutef_time_window"
                                    min={1}
                                    defaultValue={initialFormData?.brutef_time_window || 15}
                                    type="number"
                                    placeholder="in minutes..."
                                    className={`flex-1 ${errors?.brutef_time_window ? 'input-error' : ''}`}
                                    tooltip="E.g. default 3 attempts per 15 minutes"
                                />
                                <span className="text-sm text-gray-600 dark:text-gray-300 pb-2">{__("minute(s)", "brutefort")}</span>
                            </div>

                            <Input
                                ref={customErrorMessageRef}
                                className={errors?.brutef_custom_error_message ? 'input-error' : ''}
                                id="bf-custom-error-message"
                                name="brutef_custom_error_message"
                                defaultValue={initialFormData?.brutef_custom_error_message || "Too many attempts!! Try again after {{locked_out_until}}."}
                                type="text"
                                label="Custom Error Message"
                                placeholder="Too many attempts!! Try again after {{locked_out_until}}."
                                tooltip="Use {{locked_out_until}} tag to show locked out until period."
                            />
                        </div>
                    </div>

                    {/* Lockout Settings Card */}
                    <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div className="mb-4">
                            <span className="text-base font-semibold text-gray-900 dark:text-white">
                                {__("Lockout Settings", "brutefort")}
                            </span>
                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {__("Configure IP lockout behavior after failed attempts.", "brutefort")}
                            </p>
                        </div>

                        <div className="space-y-4">
                            <CheckBox
                                ref={enableLockoutRef}
                                id="bf-enable-lockout"
                                name="brutef_enable_lockout"
                                defaultChecked={initialFormData?.brutef_enable_lockout || enableLockout}
                                label="Enable lockout"
                                onChange={handleLockout}
                                tooltip="Enabling this will override requests per set time just above."
                                className={errors?.brutef_enable_lockout ? 'input-error' : ''}
                            />

                            {enableLockout && (
                                <>
                                    <div className="flex gap-2 items-end">
                                        <Input
                                            ref={lockoutDurationRef}
                                            id="bf-lockout-duration"
                                            name="brutef_lockout_duration"
                                            min={1}
                                            defaultValue={initialFormData?.brutef_lockout_duration || 5}
                                            type="text"
                                            label="Lockout Duration"
                                            placeholder="in minutes..."
                                            tooltip="How long an IP is blocked after limit (in minutes)."
                                            className={`flex-1 ${errors?.brutef_lockout_duration ? 'input-error' : ''}`}
                                        />
                                        <span className="text-sm text-gray-600 dark:text-gray-300 pb-2">{__("minute(s)", "brutefort")}</span>
                                    </div>

                                    <div className="mt-6">
                                        <span className="text-base font-medium text-gray-700 dark:text-gray-200 block mb-3">
                                            {__("Lockout Extensions", "brutefort")}
                                        </span>
                                        <CheckBox
                                            ref={enableLockoutExtensionRef}
                                            id="bf-enable-lockout-extension"
                                            name="brutef_enable_lockout_extension"
                                            defaultChecked={initialFormData?.brutef_enable_lockout_extension || enableLockoutExtension}
                                            label="Enable lockout extension"
                                            onChange={handleLockoutExtension}
                                            tooltip="Tick this if you want to extend the lockout period if the login attempt keeps on failing (in hrs)."
                                            className={errors?.bg_extend_lockout ? 'input-error' : ''}
                                        />
                                    </div>

                                    {enableLockoutExtension && (
                                        <div className="flex gap-2 items-end mt-4">
                                            <Input
                                                ref={extendLockoutDurationRef}
                                                id="bf-extend-lockout-duration"
                                                name="brutef_extend_lockout_duration"
                                                min={1}
                                                defaultValue={initialFormData?.brutef_extend_lockout_duration || 1}
                                                type="number"
                                                label="Extended Duration"
                                                placeholder="in hours..."
                                                tooltip="How long should the restriction time be extended in hours."
                                                className={`flex-1 ${errors?.brutef_extend_lockout_duration ? 'input-error' : ''}`}
                                            />
                                            <span className="text-sm text-gray-600 dark:text-gray-300 pb-2">{__("hour(s)", "brutefort")}</span>
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
});

export default RateLimit;
