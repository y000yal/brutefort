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

export type User = {
    id: number;
    name: string;
    email: string;
    role: string;
};

const roles = ['Admin', 'Editor', 'Viewer'];

export function generateUsers(count: number = 100): User[] {
    const users: User[] = [];

    for (let i = 1; i <= count; i++) {
        users.push({
            id: i,
            name: `User ${i}`,
            email: `user${i}@example.com`,
            role: roles[i % roles.length],
        });
    }

    return users;
}