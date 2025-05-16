import React from "react";
import { SpinnerProps } from "../types";

const Spinner: React.FC<SpinnerProps> = ({
                                             size = 24,
                                             color = "border-blue-500",
                                             className = "",
                                         }) => {
    return (
        <div
            className={`inline-block border-4 border-t-transparent ${color} animate-spin ${className}`}
            style={{
                width: `${size}px`,
                height: `${size}px`,
                borderRadius: '50%',
                animation: 'spin 1s radial infinite',
            }}
        />
    );
};

export default Spinner;
