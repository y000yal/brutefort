import React, { useState, useEffect } from 'react';
import Switch from './Switch'; // Import the Switch component

const ThemeToggle: React.FC = () => {
    const [isDarkMode, setIsDarkMode] = useState<boolean>(false);

    useEffect(() => {
        // Load saved theme preference from localStorage
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
        <div className="flex items-center justify-center p-4">
            <Switch isChecked={isDarkMode} onChange={toggleTheme} />
        </div>
    );
};

export default ThemeToggle;
