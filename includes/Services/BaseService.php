<?php

namespace BruteFort\Services;

class BaseService
{
    /**
     * Validates and sanitizes an array of settings.
     *
     * @param array $data An array of settings, each with 'value', 'type', and 'is_required' keys.
     *
     * @return array An associative array containing 'errors' and 'sanitized' data.
     */
    public function validate_and_sanitize_settings(array $data): array
    {
        $errors = [];
        $sanitized = [];
       
        foreach ($data['formData'] as $field => $details) {
          
            $value = $details['value'] ?? null;
            $type = $details['type'] ?? null;
            $required = $details['required'] ?? false;
            $regex_pattern = "bf_ip_address" === $field ? "/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/" : "";

            switch ($type) {
                case 'number':

                    if ($required && ($value === '' || !is_numeric($value) || $value < 1)) {
                        $errors['field'] = $field;
                        $errors['message'] = 'This field must be a valid number.';
                    }
                    $sanitized[$field] = is_numeric($value) ? (int) $value : 0;
                    break;

                case 'text':
                    $trimmed = trim((string) $value);

                    if ($required && $trimmed === '') {
                        $errors['field'] = $field;
                        $errors['message'] = 'This field cannot be empty.';
                    }
                    $sanitized[$field] = sanitize_text_field($trimmed);
                    break;

                case 'checkbox':
                    // Checkbox doesn't need validation even if required
                    $sanitized[$field] = in_array($value, ['on', '1', 1, true], true);
                    break;

                case 'regex':
                    $trimmed = trim((string) $value);

                    if ($required && $trimmed === '') {
                        $errors['field'] = $field;
                        $errors['message'] = 'This field cannot be empty.';
                        break;
                    }
                    
                    if (empty(preg_match($regex_pattern, $value))) {
                        $errors['field'] = $field;
                        $errors['message'] = 'Invalid field value.';
                    }

                    $sanitized[$field] = preg_match($regex_pattern, $value) ? $value : null;
                    break;
                default:
                    // fallback: sanitize text
                    $sanitized[$field] = sanitize_text_field(trim((string) $value));
                    break;
            }
        }

        return [
            'errors' => $errors,
            'sanitized' => $sanitized,
        ];
    }
}