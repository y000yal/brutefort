import React, {
  forwardRef,
  useMemo,
  useRef,
  useState,
  useEffect,
  use,
} from "react";
import { IpSettingsProps } from "../../types";
import Spinner from "../../components/Spinner";
import { __ } from "@wordpress/i18n";
import { CheckBox, Input, Select } from "../../components/forms";
import { useQuery } from "@tanstack/react-query";
import api from "../../axios/api";
import DataTable from "../../components/table/DataTable";
import {
  BookmarkSimple,
  CalendarCheck,
  Key,
  List,
  MapPin,
  Note,
  PencilSimple,
  PlusCircle,
  Trash,
  Warning,
} from "@phosphor-icons/react";
import CopyCell from "../../components/table/CopyCell";
import { createColumnHelper } from "@tanstack/react-table";
import { showToast } from "../../utils";
import { useQueryClient } from "@tanstack/react-query";
import { motion, AnimatePresence } from "framer-motion";

const IpSettings = forwardRef((props: IpSettingsProps, ref: React.Ref<any>) => {
  const [timestamp, setTimestamp] = useState(Math.floor(Date.now() / 1000));
  const { settings } = props;
  const indexRoute = settings?.Routes?.Index?.value;
  const deleteRoute = settings?.Routes?.Delete?.value;
  const endpoint = settings?.id;
  const ip_url = `${endpoint}${indexRoute}`;
  const columnHelper = createColumnHelper();
  const ipAddressRef = useRef<HTMLInputElement>(null);
  const [type, setType] = useState("whitelist");
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [errors, setErrors] = useState({});
  const queryClient = useQueryClient();
  const [selectedRows, setSelectedRows] = useState([]);
  const [showConfirmModal, setShowConfirmModal] = useState(false);

  const handleSelectionChange = (rows: any[]) => {
    setSelectedRows(rows);
  };

  const { data, isLoading } = useQuery({
    initialData: undefined,
    queryKey: ["ip-settings"],
    queryFn: async () => {
      const res = await api.get(ip_url);
      return res.data.data;
    },
    staleTime: Infinity, // cache forever unless manually invalidate
    enabled: !!ip_url, // only run when ip_url is defined
  });

  useEffect(() => {
    const interval = setInterval(() => {
      setTimestamp(Math.floor(Date.now() / 1000)); // Update timestamp every second
    }, 1000); // Update every second

    return () => clearInterval(interval); // Cleanup on component unmount
  }, []);
  
  const columns = useMemo(() => {
    return [
      columnHelper.display({
        id: "select",
        header: ({ table }) => (
          <input
            type="checkbox"
            checked={table.getIsAllPageRowsSelected()}
            onChange={(e) => {
              table.getToggleAllPageRowsSelectedHandler()(e);
              const selected = table
                .getSelectedRowModel()
                .rows.map((row) => row.original);
              handleSelectionChange(selected);
            }}
          />
        ),
        cell: ({ row, table }) => (
          <input
            type="checkbox"
            checked={row.getIsSelected()}
            onChange={(e) => {
              row.getToggleSelectedHandler()(e);
              const selected = table
                .getSelectedRowModel()
                .rows.map((row) => row.original);
              handleSelectionChange(selected);
            }}
          />
        ),
      }),
      columnHelper.accessor("bf_list_type", {
        header: () => (
          <>
            <BookmarkSimple size={16} weight="bold" />
            Type
          </>
        ),
        cell: (info) => (
          <span
            className={`capitalize log-status status-${
              "whitelist" === info.getValue() ? "success" : "locked"
            }`}
          >
            {info.getValue()}
          </span>
        ),
        meta: {
          filterType: "dropdown",
          filterOptions: ["Whitelist", "Blacklist"],
        },
        enableSorting: false,
      }),
      columnHelper.accessor("bf_ip_address", {
        header: () => (
          <>
            <List size={16} weight="bold" /> ipAddress
          </>
        ),
        cell: ({ getValue }) => <CopyCell value={getValue()} />,
        enableSorting: false,
      }),
      columnHelper.accessor("created_at", {
        header: () => (
          <>
            <CalendarCheck size={16} weight="bold" /> created
          </>
        ),
        cell: (info) => {
          const rawDate = new Date(info.getValue() * 1000);

          const formattedDate = rawDate.toLocaleDateString(
            undefined as unknown as string,
            {
              year: "numeric",
              month: "long",
              day: "numeric",
            }
          );

          const formattedTime = rawDate.toLocaleTimeString(
            undefined as unknown as string,
            {
              hour: "numeric",
              minute: "2-digit",
              second: "2-digit",
              hour12: true,
              timeZoneName: "short",
            }
          );

          return (
            <span className="flex flex-col gap-1 items-start text-sm text-gray-600">
              <span>{formattedDate}</span>
              <span className="text-xs text-gray-400">{formattedTime}</span>
            </span>
          );
        },
        filterFn: (row, columnId, filterValue) => {
          const timestamp = row.getValue<number>(columnId);
          const rowDate = new Date(timestamp * 1000)
            .toISOString()
            .split("T")[0];
          return rowDate === filterValue;
        },
        meta: {
          filterType: "date",
        },
        enableSorting: true,
      }),
      // other columns here...
    ];
  }, [timestamp]); // add any dependency if needed

  const handleSave = async () => {
    setIsSaving(true);
    const res = await api
      .post(ip_url, {
        formData: {
          bf_ip_address: {
            value: ipAddressRef.current?.value,
            type: "regex",
            required: true,
          },
          bf_list_type: {
            value: type,
            type: "text",
            required: true,
          },
          created_at: {
            value: timestamp,
            type: "text",
            required: true,
          },
        },
      })
      .then((response) => {
        if (response.status == 200) {
          showToast(
            response?.message ||
              __("Settings saved successfully.", "brutefort"),
            { type: "success" }
          );
          setErrors({});
          queryClient.invalidateQueries(["ip-settings"]);
        }
      })
      .catch((response) => {
        if (response.status > 200) {
          showToast(
            response?.response?.data?.message ||
              __("Settings not saved.", "brutefort"),
            { type: "error" }
          );

          setErrors(response?.response?.data || errors);
        }
      })
      .finally(() => setIsSaving(false));
  };

  const handleDeleteRow = async() => {
    const deleteUrl = `${endpoint}${deleteRoute}`;
    setIsDeleting(true);

    if(selectedRows.length < 1) {
      showToast(
          __("No row selected.", "brutefort"),
        { type: "error" }
      );
    }
    var ips = [];
    selectedRows.forEach((i)=>{
      ips.push(i?.bf_ip_address);
    });

    await api
      .delete(`${deleteUrl}/?ids=${JSON.stringify(ips)}`)
      .then((response) => {
        if (response.status == 200) {
          showToast(
            response?.message ||
              __("Records deleted successfully.", "brutefort"),
            { type: "success" }
          );
          setErrors({});
          setShowConfirmModal(false);
          queryClient.invalidateQueries(["ip-settings"]);
          setSelectedRows({});
        }
      })
      .catch((response) => {
        if (response.status > 200) {
          showToast(
            response?.response?.data?.message ||
              __("Something went wrong while deleting records.", "brutefort"),
            { type: "error" }
          );

          setErrors(response?.response?.data || errors);
        }
      })
      .finally(() => setIsDeleting(false));
  }

  return (
    <div className="flex gap-4 justify-around flex-col">
      <span className="settings-title">
        {__("Whitelist/Blacklist Settings", "brutefort")}
      </span>

      <div className="flex gap-4 items-start">
        <div className="w-[25%]">
          <Select
            label="Choose Option"
            id="bf-list-type"
            isSearchable={false}
            name="bf_list_type"
            defaultValue={{ label: "WhiteList", value: "whitelist" }}
            options={[
              { label: "WhiteList", value: "whitelist" },
              { label: "BlackList", value: "blacklist" },
            ]}
            onChange={(option) => setType(option?.value)}
          />
        </div>

        <div className="flex-1 flex gap-2 items-end justify-between">
          <Input
            ref={ipAddressRef}
            id="bf-ip-address"
            name="bf_ip_address"
            defaultValue=""
            type="text"
            label={__("IP Address", "brutefort")}
            placeholder={__("eg. 127.0.0.1", "brutefort")}
            tooltip={__(
              "Enter IP Address to whitelist/blacklist.",
              "brutefort"
            )}
            className={`${
              errors?.field === "bf_ip_address" ? "input-error" : ""
            }`}
          />
          <div className="save-btn flex gap-2 items-center">
            <button
              className="gap-2 items-center button button-primary"
              style={{ display: "flex", alignItems: "center" }}
              onClick={handleSave}
              disabled={isSaving}
            >
              {__("Add", "brutefort")}
              <PlusCircle size={16} weight="fill" />
            </button>
            {isSaving && (
              <Spinner
                size={18}
                className="rounded-lg"
                color="border-primary-light"
              />
            )}
          </div>
        </div>
      </div>
      <hr />
      {isLoading ? (
        <div className="flex items-center justify-center">
          <Spinner
            size={18}
            className="rounded-lg"
            color="border-primary-light"
          />
        </div>
      ) : (
        <>
          <div className="log-body mt-5">
            <DataTable
              data={data}
              columns={columns}
              isLoading={isLoading}
              onSelectionChange={handleSelectionChange}
            />
          </div>
          <AnimatePresence>
            {selectedRows.length > 0 && (
              <motion.div
                initial={{ opacity: 0, scale: 0 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0 }}
                transition={{
                  duration: 0.2,
                  scale: { type: "spring", visualDuration: 0.3, bounce: 0.3 },
                }}
                className="fixed bottom-6 left-[60%] bg-white/10 backdrop-blur-md border border-white/20 rounded-xl shadow-lg px-4 py-2"
              >
                <div className="flex gap-2 justify-between">
                  <Trash
                    className="text-gray-400 hover:text-red-400 transition-colors duration-100 cursor-pointer"
                    size={22}
                    weight="fill"
                    onClick={() => {
                      setShowConfirmModal(true);
                    }}
                  />
                  <PencilSimple
                    className="text-gray-400 hover:text-blue-400 transition-colors duration-100 cursor-pointer"
                    size={22}
                    weight="fill"
                    onClick={() => {}}
                  />
                </div>
              </motion.div>
            )}
          </AnimatePresence>
          <AnimatePresence>
            {showConfirmModal && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0}}
                transition={{
                  duration: 0.2,
                  scale: { type: "spring", visualDuration: 0.2, bounce: 0.2 },
                }}
                className="fixed left-[160px] inset-0 flex items-center justify-center z-50 bg-black/40"
              >
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full">
                  <h2 className="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    Confirm Delete
                  </h2>
                  <p className="text-sm text-gray-600 dark:text-gray-300 mb-6">
                    {__(
                      "Are you sure you want to delete the selected IP(s)?",
                      "brutefort"
                    )}
                  </p>
                  <div className="flex justify-end gap-2">
                    <button
                      className="px-4 py-1 text-sm rounded border cursor-pointer border-gray-300 hover:bg-white-400 dark:border-gray-600"
                      onClick={() => setShowConfirmModal(false)}
                    >
                      {__("Cancel", "brutefort")}
                    </button>
                    <button
                      className="px-4 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded cursor-pointer"
                      onClick={handleDeleteRow}
                    >
                      {__("Delete", "brutefort")}
                    </button>
                    {isDeleting && (
                        <Spinner
                            size={18}
                            className="rounded-lg"
                            color="border-primary-light"
                        />
                    )}
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </>
      )}
    </div>
  );
});
export default IpSettings;
