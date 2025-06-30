import React, { forwardRef } from "react";
import { Info } from "@phosphor-icons/react";
import { Tooltip } from "./index";
import { __ } from "@wordpress/i18n";
import ReactSelect, {
  Props as ReactSelectProps,
  GroupBase,
} from "react-select";

interface Option {
  label: string;
  value: string | number;
}

interface SelectProps
  extends Partial<ReactSelectProps<Option, boolean, GroupBase<Option>>> {
  label?: string;
  tooltip?: string;
  isMulti?: boolean;
  isSearchable?: boolean;
  isRtl?: boolean;
  className?: string;
  defaultValue?: Option | Option[]; // for single or multi-select
}

const Select = forwardRef<any, SelectProps>(
  (
    {
      label,
      tooltip,
      isMulti = false,
      isSearchable = true,
      isRtl = false,
      className = "",
      defaultValue,
      ...rest
    },
    ref
  ) => {
    return (
      <label className="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-white">
        <div className="flex items-center gap-1">
          {label && <span>{__(label, "brutefort")}</span>}
          {tooltip && (
            <Tooltip content={__(tooltip, "brutefort")}>
              <Info size={14} />
            </Tooltip>
          )}
        </div>
        <ReactSelect
          ref={ref}
          isMulti={isMulti}
          isSearchable={isSearchable}
          isRtl={isRtl}
          defaultValue={defaultValue}
          className={className}
          classNamePrefix="react-select"
          styles={{
            control: (base) => ({
              ...base,
              minHeight: "30px",
              height: "30px",
            }),
            valueContainer: (base) => ({
              ...base,
              padding: "0 10px",
            }),

            indicatorsContainer: (base) => ({
              ...base,
              height: "30px",
            }),
          }}
          {...rest}
        />
      </label>
    );
  }
);

export default Select;
