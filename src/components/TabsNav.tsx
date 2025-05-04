import React from "react";

interface TabsNavProps {
    activeTab: string;
    setActiveTab: (tab: string) => void;
    tabRefs: React.MutableRefObject<Record<string, HTMLButtonElement | null>>;
    pillStyle: React.CSSProperties;
}

import {TABS} from '../constants/tabs';


const TabsNav: React.FC<TabsNavProps> = ({activeTab, setActiveTab, tabRefs, pillStyle}) => {
    return (
        <nav className="mb-6">
            <div className="relative inline-flex bg-gray-100 p-1 rounded-xl transition-all duration-300">
                <span
                    className="absolute top-0 bottom-0 bg-white rounded-lg shadow transition-all duration-200"
                    style={{
                        ...pillStyle,
                        top: '4px',
                        height: 'calc(100% - 8px)',
                    }}
                />
                {Object.entries(TABS).map(([key, label]) => (
                    <button
                        key={key}
                        ref={(el) => (tabRefs.current[key] = el)}
                        onClick={() => setActiveTab(key)}
                        className={`relative z-10 px-6 py-2 text-sm transition-colors duration-200 hover:cursor-pointer ${
                            activeTab === key ? 'text-black' : 'text-gray hover:text-blue-600 font-medium'
                        }`}
                        style={{
                            fontWeight: activeTab === key ? 600 : 'normal',
                        }}
                    >
                        {label}
                    </button>
                ))}
            </div>
        </nav>
    );
}
export default TabsNav;
