<?php
// ### Updated:  Sun Sep 28 2025 00:37:23 CDT
// Help file: http://localhost/app5/dashboard/index.php#../app/chatgpt_chats/markdown_files/binding_and_automating_php_queries/bind_pdo_queries_class_part2_php.php

namespace RpCrudClasses;

class ValidatorHelper
{
    /**
     * Validate data with rules, messages, and optional aliases for fields.
     * @param array $data Input data to validate
     * @param array $rules Validation rules
     * @param array $messages Custom error messages (optional)
     * @param array $aliases Field name aliases (optional)
     * @return true|array Returns true if valid, or array of errors
     */
            /*
            Example:
            Update failed:

            Array
            (
                [name] => Array
                    (
                        [0] => Name is required
                    )
            )

            or:

            Update failed:

            Array
            (
                [name] => Array
                    (
                        [0] => Name is required
                    )

                [quantity] => Array
                    (
                        [0] => Quantity is required
                        [1] => Quantity must be an integer
                        [2] => Quantity must be at least 0
                    )

            )

            */
    public static function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $aliases = []
    ) {
        // Example using Valitron or similar
        $v = new \Valitron\Validator($data);

        // Apply rules
        foreach ($rules as $field => $ruleSet) {
            foreach ($ruleSet as $rule) {
                if (is_array($rule)) {
                    $method = array_shift($rule);
                    $v->rule($method, $field, ...$rule);
                } else {
                    $v->rule($rule, $field);
                    // outputs this: $v->('numeric', 'price_each')
                    // outputs this: $v->('required', 'order_id')
                }
            }
        }

        // Set custom messages if any
        if (!empty($messages)) {
            $v->message($messages);
        }

        // Set field labels (aliases) if any
        if (!empty($aliases)) {
            $v->labels($aliases);
        }

        if ($v->validate()) {
            return true;
        }

        return $v->errors();
    }
}