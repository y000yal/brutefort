import React, { useEffect } from 'react';
import { Route, Routes, useNavigate, useLocation } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import {MainLayout} from "./MainLayout";
import {Settings, Logs, About, SetupWizard} from "../../screens";
import api from "../../axios/api";

export const Router: React.FC = () => {
    const navigate = useNavigate();
    const location = useLocation();

    // Check setup wizard status
    const { data: wizardStatus, isLoading, error } = useQuery({
        queryKey: ["setup-wizard-status"],
        queryFn: async () => {
            try {
                const res = await api.get("setup-wizard/status");
                return res.data;
            } catch (err) {
                console.error("Error fetching setup wizard status:", err);
                // Return default status if API fails
                return { completed: false };
            }
        },
        retry: 1,
    });

    useEffect(() => {
        if (!isLoading && wizardStatus) {
            const completed = wizardStatus.completed === true;
            // HashRouter uses pathname for the route path (hash is handled by the router)
            const currentPath = location.pathname || '/';
            const isOnWizardPage = currentPath === '/setup-wizard';
            
            // If wizard is not completed and user is not on wizard page, redirect to wizard
            if (!completed && !isOnWizardPage) {
                navigate("/setup-wizard", { replace: true });
            }
            // If wizard is completed and user is on wizard page, redirect to settings
            else if (completed && isOnWizardPage) {
                navigate("/settings", { replace: true });
            }
        }
    }, [wizardStatus, isLoading, navigate, location]);

    // Show loading state while checking wizard status
    if (isLoading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p className="mt-4 text-gray-600 dark:text-gray-400">Loading...</p>
                </div>
            </div>
        );
    }

    // Determine if wizard is completed
    // Default to false if status is not available (safer for security)
    const wizardCompleted = wizardStatus?.completed === true;
    const currentPath = location.pathname || '/';
    const isOnWizardPage = currentPath === '/setup-wizard';

    // If wizard is not completed, only show the setup wizard route
    if (!wizardCompleted) {
        // Only redirect if we're not already on the wizard page
        if (!isOnWizardPage) {
            return (
                <Routes>
                    <Route path="*" element={<SetupWizard />} />
                </Routes>
            );
        }
        return (
            <Routes>
                <Route path="/setup-wizard" element={<SetupWizard />} />
            </Routes>
        );
    }

    // If wizard is completed, show all routes with MainLayout
    return (
        <Routes>
            <Route path="/" element={<MainLayout />}>
                <Route index element={<Settings />} />  
                <Route path="settings" element={<Settings />} />
                <Route path="logs" element={<Logs />} />
                <Route path="about-us" element={<About />} />
            </Route>
            {/* Redirect setup wizard to settings if completed */}
            <Route path="/setup-wizard" element={<MainLayout />}>
                <Route index element={<Settings />} />
            </Route>
        </Routes>
    );
};
