<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        // Image : requise en création (POST), optionnelle en édition
        $imageRules = $this->isMethod('post')
            ? ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048']
            : ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'];

        $rules = [
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'description'    => ['required', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'discount_type'  => ['nullable', Rule::in(['fixed', 'percent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'image'          => $imageRules,
        ];

        // Rendre la valeur de remise obligatoire si un type est sélectionné
        if ($this->filled('discount_type')) {
            $rules['discount_value'][] = 'required';
            if ($this->discount_type === 'percent') {
                $rules['discount_value'][] = 'max:100';
            } elseif ($this->discount_type === 'fixed') {
                // remise fixe ≤ prix
                $rules['discount_value'][] = 'lte:price';
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Slug auto si vide
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Normalisation types
        if (array_key_exists('price', $data) && $data['price'] !== '' && $data['price'] !== null) {
            $data['price'] = (float) str_replace(',', '.', (string) $data['price']);
        }
        if (array_key_exists('stock', $data) && $data['stock'] !== '' && $data['stock'] !== null) {
            $data['stock'] = (int) $data['stock'];
        }
        if (array_key_exists('category_id', $data) && $data['category_id'] !== '' && $data['category_id'] !== null) {
            $data['category_id'] = (int) $data['category_id'];
        }

        $this->replace($data);
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Le nom est requis.',
            'description.required'     => 'La description est requise.',
            'price.required'           => 'Le prix est requis.',
            'price.numeric'            => 'Le prix doit être un nombre.',
            'stock.required'           => 'Le stock est requis.',
            'stock.integer'            => 'Le stock doit être un entier.',
            'category_id.required'     => 'La catégorie est requise.',
            'category_id.exists'       => 'La catégorie sélectionnée est invalide.',
            'discount_type.in'         => 'Le type de remise est invalide.',
            'discount_value.required'  => 'La valeur de remise est requise pour le type choisi.',
            'discount_value.numeric'   => 'La valeur de remise doit être un nombre.',
            'discount_value.max'       => 'Le pourcentage ne peut pas dépasser 100 %.',
            'discount_value.lte'       => 'La remise fixe ne peut pas dépasser le prix.',
            'image.required'           => 'L’image est requise à la création.',
            'image.image'              => 'Le fichier doit être une image.',
            'image.mimes'              => 'Formats acceptés : jpeg, png, jpg, gif, webp.',
            'image.max'                => 'L’image ne doit pas dépasser 2 Mo.',
        ];
    }

    public function attributes(): array
    {
        // Pour que les messages automatiques utilisent des libellés FR
        return [
            'name'         => 'nom',
            'slug'         => 'slug',
            'description'  => 'description',
            'price'        => 'prix',
            'stock'        => 'stock',
            'category_id'  => 'catégorie',
            'discount_type'=> 'type de remise',
            'discount_value'=> 'valeur de remise',
            'image'        => 'image',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Upload image si fournie
        if ($this->hasFile('image')) {
            $file = $this->file('image');

            // 🔥 SÉCURITÉ : Générer un nom sûr au lieu d'utiliser le nom original
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;

            $file->move(public_path('images/produits'), $filename);
            $validated['image'] = $filename;
        } else {
            // En édition : ne pas toucher à l'image si rien de nouveau
            if ($this->isMethod('put') || $this->isMethod('patch')) {
                unset($validated['image']);
            }
        }

        return $validated;
    }
}
