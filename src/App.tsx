import React, {useEffect, useRef, useState} from "react";
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {ReactQueryDevtools} from '@tanstack/react-query-devtools';

import './styles/admin.css';
import {HashRouter, Routes, Route, Navigate} from 'react-router-dom';
import {Router} from "./components/router/Router";
import {IconInjector} from "./components/IconInjector";
import {ToastContainer} from "react-toastify";

const queryClient = new QueryClient();

export const App: React.FC = () => {
    return (
        <QueryClientProvider client={queryClient}>
            <HashRouter>
                <IconInjector/>
                <Router/>
                <ToastContainer
                    position="top-right"
                    autoClose={3000}
                    hideProgressBar={false}
                    newestOnTop
                    closeOnClick
                    pauseOnFocusLoss
                    draggable
                    pauseOnHover
                />
            </HashRouter>
            <ReactQueryDevtools initialIsOpen={false} />
        </QueryClientProvider>
    );
};