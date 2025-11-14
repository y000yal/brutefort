import React, { useState, useEffect } from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import api from "../axios/api";
import { __ } from "@wordpress/i18n";
import { ShieldSlash, CheckCircle, Spinner as SpinnerIcon } from "@phosphor-icons/react";
import Input from "../components/forms/Input";
import { showToast } from "../utils";
import { useNavigate } from "react-router-dom";

declare const BruteFortData: { 
  restUrl: string;
  nonce: string;
  setupWizardCompleted?: boolean;
};

const SetupWizard: React.FC = () => {
  const [ipAddress, setIpAddress] = useState("");
  const [isSaving, setIsSaving] = useState(false);
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  // Fetch current IP
  const { data: currentIpData, isLoading: isLoadingIp } = useQuery({
    queryKey: ["current-ip"],
    queryFn: async () => {
      const res = await api.get("ip-settings/current-ip");
      return res.data;
    },
  });

  useEffect(() => {
    if (currentIpData?.ip) {
      setIpAddress(currentIpData.ip);
    }
  }, [currentIpData]);

  const handleSave = async () => {
    if (!ipAddress.trim()) {
      showToast(__("Please enter an IP address.", "brutefort"), { type: "error" });
      return;
    }

    setIsSaving(true);
    const timestamp = Math.floor(Date.now() / 1000);

    try {
      const response = await api.post("ip-settings/setup-wizard-whitelist", {
        ip_address: ipAddress.trim(),
        created_at: timestamp,
      });

      if (response.status === 200) {
        showToast(
          response.data?.message || __("IP whitelisted successfully!", "brutefort"),
          { type: "success" }
        );

        // Invalidate queries and wait for refetch
        queryClient.invalidateQueries({ queryKey: ["ip-settings"] });
        queryClient.invalidateQueries({ queryKey: ["setup-wizard-status"] }).then(() => {
          // Router's useEffect will automatically redirect when status updates
          // But we also navigate here to ensure immediate redirect
          navigate("/settings", { replace: true });
        });
      }
    } catch (error: any) {
      const errorMessage = error?.response?.data?.message || __("Failed to whitelist IP address.", "brutefort");
      showToast(errorMessage, { type: "error" });
      setIsSaving(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 p-4">
      <div className="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <div className="text-center mb-8">
          <div className="flex justify-center mb-4">
            <ShieldSlash size={64} weight="fill" color="#e66a6f" />
          </div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {__("Welcome to BruteFort", "brutefort")}
          </h1>
          <p className="text-gray-600 dark:text-gray-400">
            {__("Let's get started by whitelisting your current IP address.", "brutefort")}
          </p>
        </div>

        <div className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {__("Your Current IP Address", "brutefort")}
            </label>
            {isLoadingIp ? (
              <div className="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <SpinnerIcon className="animate-spin" size={20} />
                <span>{__("Detecting your IP address...", "brutefort")}</span>
              </div>
            ) : (
              <div className="p-3 bg-gray-100 dark:bg-gray-700 rounded-md text-gray-700 dark:text-gray-300 font-mono">
                {currentIpData?.ip || __("Unable to detect IP", "brutefort")}
              </div>
            )}
          </div>

          <div>
            <Input
              id="ip-address"
              label={__("IP Address to Whitelist", "brutefort")}
              type="text"
              value={ipAddress}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setIpAddress(e.target.value)}
              placeholder={__("Enter IP address", "brutefort")}
              disabled={isLoadingIp}
              className="w-full"
            />
            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
              {__("This IP address will be added to your whitelist. You can modify it if needed.", "brutefort")}
            </p>
          </div>

          <button
            onClick={handleSave}
            disabled={isSaving || isLoadingIp || !ipAddress.trim()}
            className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold py-3 px-4 rounded-md transition-colors duration-200 flex items-center justify-center gap-2"
          >
            {isSaving ? (
              <>
                <SpinnerIcon className="animate-spin" size={20} />
                <span>{__("Saving...", "brutefort")}</span>
              </>
            ) : (
              <>
                <CheckCircle size={20} />
                <span>{__("Save & Continue", "brutefort")}</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default SetupWizard;

