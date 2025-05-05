import React from "react";

export interface TabsNavProps {
    activeTab: string;
    setActiveTab: (tab: string) => void;
    tabRefs: React.MutableRefObject<Record<string, HTMLButtonElement | null>>;
    pillStyle: React.CSSProperties;
}

export interface SwitchProps {
    isChecked: boolean;
    label?: string;
    onChange: () => void;
}
