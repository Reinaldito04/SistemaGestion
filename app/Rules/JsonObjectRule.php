<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class JsonObjectRule implements Rule
{
    /**
     * Verifica si el valor es un objeto JSON.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Si el valor ya es un array, significa que Laravel ya lo procesó correctamente.
        if (is_array($value)) {
            return true;
        }

        // Si el valor es un string, intenta decodificarlo como JSON
        if (is_string($value)) {
            $decoded = json_decode($value);

            // Verifica que el JSON decodificado sea un objeto
            return is_object($decoded);
        }

        // Si no es ni array ni string, falla la validación
        return false;
    }

    /**
     * Mensaje de error.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid JSON object.';
    }
}