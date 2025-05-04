import React, {useState, useRef, useEffect} from 'react';
import {createRoot} from 'react-dom/client';
import './styles/admin.css';
import TabsNav from "./components/TabsNav";
import {General} from "./components/tabs"; // Tailwind import
import ThemeToggle from './components/ThemeToggle';



const App: React.FC = () => {
    const [activeTab, setActiveTab] = useState('general');
    const tabRefs = useRef<Record<string, HTMLButtonElement | null>>({});
    const [pillStyle, setPillStyle] = useState<React.CSSProperties>({});

    useEffect(() => {
        const el = tabRefs.current[activeTab];
        if (el) {
            setPillStyle({
                left: `${el.offsetLeft}px`,
                width: `${el.offsetWidth}px`,
                top: '4px',
                height: 'calc(100% - 8px)',
            });
        }
    }, [activeTab]);
    useEffect(() => {
        const stored = localStorage.getItem('theme');
        if (
            stored === 'dark' ||
            (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        }
    }, []);
    const renderContent = () => {
        switch (activeTab) {
            case 'general':
                return <General />;
            default:
                return null;
        }
    };

    return (

        <div className="p-6 max-w-8xl force-tailwind">

            <div className="flex justify-between mb-4">
                <h1 className="text-3xl font-bold mb-6 dark:text-gray-200">🛡️ BruteFort Settings</h1>
                <ThemeToggle />
            </div>
            <TabsNav activeTab={activeTab} setActiveTab={setActiveTab} tabRefs={tabRefs} pillStyle={pillStyle}/>
            <div className="bg-white p-6 rounded-md shadow">{renderContent()}</div>
        </div>
    );
};

const root = document.getElementById('brutefort-admin-app');
if (root) {
    createRoot(root).render(<App/>);
}
