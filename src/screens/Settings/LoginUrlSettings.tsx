import React, { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { __ } from "@wordpress/i18n";
import { SettingComponentProps } from "../../types";
import api from "../../axios/api";
import { showToast } from "../../utils";
import Spinner from "../../components/Spinner";
import { Link } from "@phosphor-icons/react";

const LoginUrlSettings = React.forwardRef<any, SettingComponentProps>(
    ({ settings, errors }, ref) => {
        const [isLoading, setIsLoading] = useState(true);
        const {
            register,
            handleSubmit,
            setValue,
            watch,
            getValues,
            formState: { errors: formErrors }
        } = useForm();

        // Expose getFormData to parent
        React.useImperativeHandle(ref, () => ({
            getFormData: () => getValues(),
        }));

        useEffect(() => {
            const fetchSettings = async () => {
                try {
                    const response = await api.get(`${settings.id}${settings.Routes.Index.value}`);
                    if (response.data) {
                        const data = response.data;
                        setValue("enabled", data.enabled === "1" || data.enabled === true);
                        setValue("slug", data.slug || "my-login");
                    }
                } catch (error) {
                    showToast(__("Failed to load settings", "brutefort"), { type: "error" });
                } finally {
                    setIsLoading(false);
                }
            };

            fetchSettings();
        }, [settings.id, settings.Routes.Index.value, setValue]);

        if (isLoading) {
            return (
                <div className="flex justify-center items-center p-10">
                    <Spinner size={30} color="border-blue-500" />
                </div>
            );
        }

        return (
            <div className="flex flex-col gap-5">
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                    <div className="mb-6">
                        <div className="flex items-center gap-2 mb-3">
                            <div className="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded text-blue-600 dark:text-blue-400">
                                <Link size={18} weight="bold" />
                            </div>
                            <span className="text-base font-semibold text-gray-900 dark:text-white">
                                {__("Custom Login URL", "brutefort")}
                            </span>
                        </div>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            {__("Hide your login page to prevent automated attacks.", "brutefort")}
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div>
                                <label className="font-medium text-gray-700 dark:text-gray-200 block">
                                    {__("Enable Custom Login URL", "brutefort")}
                                </label>
                                <span className="text-sm text-gray-500 dark:text-gray-400">
                                    {__("Turn on to use a custom slug for login.", "brutefort")}
                                </span>
                            </div>
                            <label className="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input
                                    type="checkbox"
                                    className="sr-only peer"
                                    {...register("enabled")}
                                />
                                <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        {watch("enabled") && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    {__("Login Slug", "brutefort")}
                                </label>
                                <div className="flex items-stretch">
                                    <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-sm whitespace-nowrap">
                                        {window.location.origin}/
                                    </span>
                                    <input
                                        type="text"
                                        {...register("slug", {
                                            required: __("Slug is required", "brutefort"),
                                            pattern: {
                                                value: /^[a-z0-9-]+$/,
                                                message: __("Only lowercase letters, numbers, and hyphens are allowed", "brutefort")
                                            }
                                        })}
                                        className="block w-full rounded-none rounded-r-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 border h-[38px]"
                                        placeholder="my-login"
                                    />
                                </div>
                                {formErrors.slug && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {formErrors.slug.message as string}
                                    </p>
                                )}
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {__("Important: Do not forget this slug! If you do, you may be locked out.", "brutefort")}
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }
);

export default LoginUrlSettings;
