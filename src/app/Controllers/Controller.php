<?php

namespace App\Controllers;

use App\Utils\HttpStatus;

/**
 * Base Controller
 */
abstract class Controller
{
    /**
     * Set JSON response headers
     *
     * @param int $cacheTime Cache time in seconds (default: 300)
     * @return void
     */
    protected function setJsonHeaders(int $cacheTime = 300): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header("Cache-Control: public, max-age=$cacheTime");
    }

    /**
     * Send success response
     *
     * @param mixed $data Data to include in response
     * @param int $statusCode HTTP status code (default: 200 OK)
     * @param array $meta Additional metadata
     * @return void
     */
    protected function sendSuccessResponse(
        mixed $data,
        int   $statusCode = HttpStatus::OK,
        array $meta = []
    ): void
    {
        http_response_code($statusCode);

        $response = [
            'success'   => true,
            'data'      => $data,
            'timestamp' => date('c')
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

        echo json_encode($response, $flags);
    }

    /**
     * Send error response
     *
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     * @param array $details Additional error details
     * @return void
     */
    protected function sendErrorResponse(
        int $statusCode,
        string $message,
        array $details = []
    ): void
    {
        http_response_code($statusCode);

        $response = [
            'success' => false,
            'error' => [
                'code' => $statusCode,
                'message' => $message
            ],
            'timestamp' => date('c')
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

        echo json_encode($response, $flags);
    }

    /**
     * Handle OPTIONS request for CORS
     *
     * @return void
     */
    public function handleOptions(): void
    {
        http_response_code(HttpStatus::OK);
//        $response = [
//            'success' => true,
//            'message' => 'CORS preflight'
//        ];
//        $flags    = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
//        echo json_encode($response, $flags);
    }

    /**
     * Validate request parameters
     *
     * @param array $params Parameters to validate
     * @param array $rules Validation rules
     * @return array|null Validation errors or null if valid
     */
    protected function validateParams(array $params, array $rules): ?array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            // Required check
            if (isset($rule['required']) && $rule['required'] && !isset($params[$field])) {
                $errors[$field] = "Field '$field' is required";
                continue;
            }

            // Skip validation if field is not present and not required
            if (!isset($params[$field])) {
                continue;
            }

            $value = $params[$field];

            // Type check
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'int':
                    case 'integer':
                        if (!is_numeric($value) || (int)$value != $value) {
                            $errors[$field] = "Field '$field' must be an integer";
                        }
                        break;
                    case 'float':
                    case 'double':
                        if (!is_numeric($value)) {
                            $errors[$field] = "Field '$field' must be a number";
                        }
                        break;
                    case 'bool':
                    case 'boolean':
                        if (!is_bool($value) && $value !== '0' && $value !== '1' &&
                            $value !== 0 && $value !== 1 &&
                            strtolower($value) !== 'true' && strtolower($value) !== 'false') {
                            $errors[$field] = "Field '$field' must be a boolean";
                        }
                        break;
                    case 'string':
                        if (!is_string($value)) {
                            $errors[$field] = "Field '$field' must be a string";
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $errors[$field] = "Field '$field' must be an array";
                        }
                        break;
                }
            }

            // Min/max value check for numbers
            if (is_numeric($value)) {
                if (isset($rule['min']) && $value < $rule['min']) {
                    $errors[$field] = "Field '$field' must be at least {$rule['min']}";
                }
                if (isset($rule['max']) && $value > $rule['max']) {
                    $errors[$field] = "Field '$field' must be at most {$rule['max']}";
                }
            }

            // Min/max length check for strings
            if (is_string($value)) {
                if (isset($rule['minLength']) && strlen($value) < $rule['minLength']) {
                    $errors[$field] = "Field '$field' must be at least {$rule['minLength']} characters long";
                }
                if (isset($rule['maxLength']) && strlen($value) > $rule['maxLength']) {
                    $errors[$field] = "Field '$field' must be at most {$rule['maxLength']} characters long";
                }

                // Pattern check
                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                    $errors[$field] = "Field '$field' has an invalid format";
                }
            }

            // Enum check
            if (isset($rule['enum']) && !in_array($value, $rule['enum'])) {
                $errors[$field] = "Field '$field' must be one of: " . implode(', ', $rule['enum']);
            }

            // Custom validation
            if (isset($rule['custom']) && is_callable($rule['custom'])) {
                $customError = $rule['custom']($value);
                if ($customError !== null) {
                    $errors[$field] = $customError;
                }
            }
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Parse JSON request body
     *
     * @return array|null Parsed JSON data or null on error
     */
    protected function parseJsonBody(): ?array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return null;
        }

        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * Get request parameter from various sources
     *
     * @param string $name Parameter name
     * @param mixed|null $default Default value if parameter is not found
     * @param string $source Source of parameter (get, post, json, any)
     * @return mixed Parameter value or default
     */
    protected function getParam(string $name, mixed $default = null, string $source = 'any'): mixed
    {
        $source = strtolower($source);

        // Check GET parameters
        if ($source === 'get' || $source === 'any') {
            if (isset($_GET[$name])) {
                return $_GET[$name];
            }
        }

        // Check POST parameters
        if ($source === 'post' || $source === 'any') {
            if (isset($_POST[$name])) {
                return $_POST[$name];
            }
        }

        // Check JSON body
        if ($source === 'json' || $source === 'any') {
            static $jsonData = null;
            if ($jsonData === null) {
                $jsonData = $this->parseJsonBody() ?? [];
            }

            if (isset($jsonData[$name])) {
                return $jsonData[$name];
            }
        }

        return $default;
    }
}

