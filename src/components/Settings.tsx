import React from 'react';

const Settings: React.FC = () => {
  return (
    <div className="brutefort-settings">
      <h1>BruteFort Settings</h1>
      <p>Configure your security settings and preferences.</p>
      
      <form className="brutefort-form">
        <div className="brutefort-form-group">
          <label className="brutefort-form-label" htmlFor="max_attempts">
            Maximum Login Attempts
          </label>
          <input
            type="number"
            id="max_attempts"
            className="brutefort-form-input"
            defaultValue="5"
            min="1"
            max="20"
          />
          <div className="brutefort-form-help">
            Number of failed login attempts before blocking an IP address.
          </div>
        </div>
        
        <div className="brutefort-form-group">
          <label className="brutefort-form-label" htmlFor="lockout_duration">
            Lockout Duration (minutes)
          </label>
          <input
            type="number"
            id="lockout_duration"
            className="brutefort-form-input"
            defaultValue="30"
            min="5"
            max="1440"
          />
          <div className="brutefort-form-help">
            How long to block an IP address after exceeding the maximum attempts.
          </div>
        </div>
        
        <div className="brutefort-form-group">
          <label className="brutefort-form-label" htmlFor="whitelist_ips">
            Whitelisted IPs
          </label>
          <textarea
            id="whitelist_ips"
            className="brutefort-form-textarea"
            rows={4}
            placeholder="Enter IP addresses, one per line"
          />
          <div className="brutefort-form-help">
            IP addresses that will never be blocked. One IP per line.
          </div>
        </div>
        
        <div className="brutefort-form-group">
          <label className="brutefort-form-label" htmlFor="blacklist_ips">
            Blacklisted IPs
          </label>
          <textarea
            id="blacklist_ips"
            className="brutefort-form-textarea"
            rows={4}
            placeholder="Enter IP addresses, one per line"
          />
          <div className="brutefort-form-help">
            IP addresses that are permanently blocked. One IP per line.
          </div>
        </div>
        
        <div className="brutefort-form-group">
          <label className="brutefort-form-label" htmlFor="enable_logging">
            <input
              type="checkbox"
              id="enable_logging"
              defaultChecked
            />
            Enable Security Logging
          </label>
          <div className="brutefort-form-help">
            Log all security events for monitoring and analysis.
          </div>
        </div>
        
        <div className="brutefort-form-actions">
          <button type="submit" className="brutefort-btn brutefort-btn-primary">
            Save Settings
          </button>
          <button type="button" className="brutefort-btn brutefort-btn-secondary">
            Reset to Defaults
          </button>
        </div>
      </form>
    </div>
  );
};

export default Settings;
