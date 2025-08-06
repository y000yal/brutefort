import React, {useState, useRef, useEffect} from 'react';
import {createRoot} from 'react-dom/client';
import {App} from "./App";

// HMR support
if (module.hot) {
    module.hot.accept();
}

const root = document.getElementById('brutefort-admin-app');
if (root) {
    createRoot(root).render(<App/>);
}
