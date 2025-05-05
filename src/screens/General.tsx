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
                <p className="text-primary">Primary Text</p>
                <p className="bg-primary-light">Light Background</p>
                <p className="hover:text-primary-dark">Hover for Dark Primary</p>
            </div>
        </div>
    );
};

export default GeneralTab;

