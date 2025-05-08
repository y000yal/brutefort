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
export type InputProps = React.InputHTMLAttributes<HTMLInputElement> & {
    label?: string;
    tooltip?: string;
};

export type RadioProps = React.InputHTMLAttributes<HTMLInputElement> & {
    label?: string;
    tooltip?: string;
};
export type CheckBoxProps = React.InputHTMLAttributes<HTMLInputElement> & {
    label?: string;
    tooltip?: string;
};
