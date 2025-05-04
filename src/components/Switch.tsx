import React from 'react';

interface SwitchProps {
    isChecked: boolean;
    onChange: () => void;
}

const Switch: React.FC<SwitchProps> = ({ isChecked, onChange }) => {
    return (
        <label className="inline-flex items-center cursor-pointer">
            <span className="mr-2 text-sm dark:text-white">Dark Mode</span>
            <div
                className={`relative inline-block w-8 h-4 transition duration-200 ease-in-out rounded-full ${
                    isChecked ? 'bg-blue-600' : 'bg-gray-400'
                }`}
            >
                <input
                    type="checkbox"
                    className="opacity-0 w-0 h-0"
                    checked={isChecked}
                    onChange={onChange}
                />
                <span
                    className={`absolute top-0 left-0 w-4 h-4 bg-white rounded-full transition duration-200 ease-in-out ${
                        isChecked ? 'transform translate-x-full' : ''
                    }`}
                />
            </div>
        </label>
    );
};

export default Switch;
