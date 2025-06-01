// utils/showToast.ts
import { toast, ToastOptions, TypeOptions } from "react-toastify";

interface ShowToastOptions {
    type?: TypeOptions;
    position?: ToastOptions["position"];
    duration?: number;
    hideCloseButton?: boolean;
    hideProgressBar?: boolean;
}

export const showToast = (
    message: string,
    {
        type = "default",
        position = "bottom-right",
        duration = 3000,
        hideCloseButton = true,
        hideProgressBar= true
    }: ShowToastOptions = {}
) => {
    toast(message, {
        type,
        position,
        autoClose: duration,
        closeButton: !hideCloseButton,
        hideProgressBar: hideProgressBar
    });
};

export const  formatDate = (timestamp: string) => {
    const date = new Date(timestamp);
    return date.toLocaleString(undefined, {
        dateStyle: "medium",
        timeStyle: "short",
    });
};