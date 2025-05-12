import React from "react";
import {Gauge, MapPin} from "@phosphor-icons/react";
import RateLimit from "../screens/Settings/RateLimit";
import IpSettings from "../screens/Settings/IpSettings";

export const SETTINGS: Record<string, { id: string, label: string; icon: React.ElementType; component: React.ComponentType, description: string }> = {
    rateLimitSettings: {
        id: "rate-limit-settings",
        label: "Rate Limit Settings",
        icon: Gauge,
        component: RateLimit,
        description: "All settings related with rate limiting, intervals , limit extensions can be found here."
    },
    ipSettings: {
        id: "ip-settings",
        label: "IP Settings",
        icon: MapPin,
        component: IpSettings,
        description: "Ip settings, from whitelisting to direct blacklisting can be found in this section."
    },
};

