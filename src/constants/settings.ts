import React from "react";
import {Gauge, ReadCvLogo, MapPinArea} from "@phosphor-icons/react";
import RateLimit from "../screens/Settings/RateLimit";
import IpSettings from "../screens/Settings/IpSettings";

export const SETTINGS: Record<string, { label: string; icon: React.ElementType; component: React.ComponentType }> = {
    rateLimitSettings: {
        label: "Rate Limit Settings",
        icon: Gauge,
        component: RateLimit,
    },
    ipSettings: {
        label: "IP Settings",
        icon: MapPinArea,
        component: IpSettings,
    },
};

