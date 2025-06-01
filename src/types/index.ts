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

export interface SpinnerProps {
    size?: number;
    color?: string;
    className?: string;
    borderRadius?: string;
}

export type RateLimitRef = {
    getFormData: () => any;
};

export interface RateLimitProps {
    errors?: Record<string, string>;
    settings?: {
        id: string,
        label: string;
        icon: React.ElementType;
        component: SettingComponentType,
        description: string,
        Routes: Record<string, any>
    };
}

export type SettingComponentType = React.ForwardRefExoticComponent<RateLimitProps & React.RefAttributes<RateLimitRef>>;

export interface SlidePanelProps {
    data: any | null;
    onClose: () => void;
    fetchDetailRoute?: string | null
}
export interface LogDetailsInterface {
    onClose: () => void;
    isLoading?: boolean;
    details: object | null;
}