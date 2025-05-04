import React from "react";

const GeneralTab = () => {
    return (
        <div className="space-y-4 bg-white dark:bg-black p-6 rounded-md shadow transition-colors duration-300">
            <h2 className="text-xl font-semibold text-gray-800 dark:text-white">General Settings</h2>
            <p className="text-gray-600 dark:text-gray-300">
                Configure brute-force protection settings such as login limits, IP bans, and more.
            </p>

            {/* Example setting field */}
            <div className="flex items-center justify-between">
                <label className="text-gray-700 dark:text-gray-200 font-medium">Enable Protection</label>
                <input type="checkbox" className="toggle toggle-success" />
            </div>
        </div>
    );
};

export default GeneralTab;

