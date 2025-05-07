import React from "react";
import {Gauge, ReadCvLogo, MapPinArea } from "@phosphor-icons/react";

export const SETTINGS: Record<string, { label: string; icon: React.ElementType  }> = {
    rateLimitSettings: {
        label: 'Rate Limit Settings',
        icon: Gauge,
    },
    ipSettings: {
        label: 'Ip Settings',
        icon: MapPinArea ,
    }
};
