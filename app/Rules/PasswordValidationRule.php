<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordValidationRule implements Rule
{
    private $error;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Asegúrate de que la contraseña tenga al menos 8 caracteres
        if (strlen($value) < 8) {
            $this->error = 'La contraseña debe tener al menos 8 caracteres.';
            return false;
        }

        // Asegúrate de que la contraseña tenga al menos una letra minúscula
        if (!preg_match('/[a-z]/', $value)) {
            $this->error = 'La contraseña debe contener al menos una letra minúscula.';
            return false;
        }

        // Asegúrate de que la contraseña tenga al menos una letra mayúscula
        if (!preg_match('/[A-Z]/', $value)) {
            $this->error = 'La contraseña debe contener al menos una letra mayúscula.';
            return false;
        }

        // Asegúrate de que la contraseña tenga al menos un número
        if (!preg_match('/[0-9]/', $value)) {
            $this->error = 'La contraseña debe contener al menos un número.';
            return false;
        }

        // Asegúrate de que la contraseña tenga al menos un carácter especial
        if (!preg_match('/[_\W]/', $value)) {
            $this->error = 'La contraseña debe contener al menos un carácter especial.';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error ?: 'La contraseña debe tener al menos 8 caracteres y contener al menos una letra minúscula, una letra mayúscula, un número y un carácter especial.';
    }
}
