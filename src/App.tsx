import React, {useEffect, useRef, useState} from "react";

import './styles/admin.css';
import {HashRouter, Routes, Route, Navigate} from 'react-router-dom';
import {Router} from "./components/router/Router";
import {IconInjector} from "./components/IconInjector";
import {ToastContainer} from "react-toastify";

export const App: React.FC = () => {
    return (
        <HashRouter>
            <IconInjector />
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
    );
};