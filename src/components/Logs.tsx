import React from 'react';

const Logs: React.FC = () => {
  return (
    <div className="brutefort-logs">
      <h1>Security Logs</h1>
      <p>Monitor all security events and login attempts.</p>
      
      <div className="brutefort-logs-controls">
        <button className="brutefort-btn brutefort-btn-secondary">
          Refresh Logs
        </button>
        <button className="brutefort-btn brutefort-btn-secondary">
          Export Logs
        </button>
        <button className="brutefort-btn brutefort-btn-danger">
          Clear Logs
        </button>
      </div>
      
      <div className="brutefort-logs-filters">
        <select className="brutefort-form-select">
          <option value="">All Events</option>
          <option value="login_failed">Failed Login</option>
          <option value="ip_blocked">IP Blocked</option>
          <option value="ip_whitelisted">IP Whitelisted</option>
          <option value="ip_blacklisted">IP Blacklisted</option>
        </select>
        
        <input
          type="date"
          className="brutefort-form-input"
          placeholder="From Date"
        />
        
        <input
          type="date"
          className="brutefort-form-input"
          placeholder="To Date"
        />
      </div>
      
      <table className="brutefort-table">
        <thead>
          <tr>
            <th>Date/Time</th>
            <th>Event Type</th>
            <th>IP Address</th>
            <th>Username</th>
            <th>Details</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colSpan={6} style={{ textAlign: 'center', padding: '2rem' }}>
              No security events logged yet.
            </td>
          </tr>
        </tbody>
      </table>
      
      <div className="brutefort-logs-pagination">
        <span>Showing 0 of 0 entries</span>
      </div>
    </div>
  );
};

export default Logs;
