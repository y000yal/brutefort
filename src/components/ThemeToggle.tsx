import React, { useState, useEffect } from 'react';
import { Moon, Sun } from '@phosphor-icons/react';

const ThemeToggle: React.FC = () => {
    const [isDarkMode, setIsDarkMode] = useState<boolean>(false);

    useEffect(() => {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            setIsDarkMode(true);
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }, []);

    const toggleTheme = () => {
        const newTheme = !isDarkMode ? 'dark' : 'light';
        setIsDarkMode(!isDarkMode);
        localStorage.setItem('theme', newTheme);
        document.documentElement.classList.toggle('dark', !isDarkMode);
    };

    return (
        <button
            aria-label="Toggle theme"
            onClick={toggleTheme}
            className={`relative cursor-pointer flex items-center justify-center w-12 h-12 rounded-full shadow-lg border border-gray-200 dark:border-gray-700
                bg-white dark:bg-gray-900 transition-all duration-300 hover:scale-105 focus:outline-none`}
        >
            <span className="absolute inset-0 flex items-center justify-center transition-opacity duration-300">
                {isDarkMode ? (
                    <Moon size={26} weight="fill" className="text-blue-400" />
                ) : (
                    <Sun size={26} weight="fill" className="text-yellow-400" />
                )}
            </span>
        </button>
    );
};

export default ThemeToggle;