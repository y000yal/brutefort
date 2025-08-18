import React from 'react';
import { Link, useLocation } from 'react-router-dom';

const Navigation: React.FC = () => {
  const location = useLocation();
  
  const isActive = (path: string) => {
    return location.pathname === path;
  };
  
  return (
    <nav className="brutefort-nav">
      <div className="brutefort-nav-header">
        <h2 className="brutefort-nav-title">BruteFort</h2>
        <p style={{ margin: '0.5rem 0 0 0', fontSize: '0.875rem', color: '#b4b9be' }}>
          Security Plugin
        </p>
      </div>
      
      <ul className="brutefort-nav-menu">
        <li className="brutefort-nav-item">
          <Link 
            to="/" 
            className={`brutefort-nav-link ${isActive('/') ? 'active' : ''}`}
          >
            Dashboard
          </Link>
        </li>
        
        <li className="brutefort-nav-item">
          <Link 
            to="/settings" 
            className={`brutefort-nav-link ${isActive('/settings') ? 'active' : ''}`}
          >
            Settings
          </Link>
        </li>
        
        <li className="brutefort-nav-item">
          <Link 
            to="/logs" 
            className={`brutefort-nav-link ${isActive('/logs') ? 'active' : ''}`}
          >
            Security Logs
          </Link>
        </li>
      </ul>
      
      <div style={{ 
        marginTop: 'auto', 
        padding: '1rem 1.5rem', 
        borderTop: '1px solid #404040',
        fontSize: '0.75rem',
        color: '#b4b9be'
      }}>
        <p style={{ margin: '0 0 0.5rem 0' }}>Version 1.0.0</p>
        <p style={{ margin: 0 }}>
          <a 
            href="https://brutefort.com/support/" 
            target="_blank" 
            rel="noopener noreferrer"
            style={{ color: '#0073aa', textDecoration: 'none' }}
          >
            Get Support
          </a>
        </p>
      </div>
    </nav>
  );
};

export default Navigation;
