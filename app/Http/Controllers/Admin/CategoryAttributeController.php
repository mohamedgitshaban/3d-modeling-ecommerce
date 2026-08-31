<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryAttribute;
use App\Models\CategoryAttributeGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    public function store(Request $request, CategoryAttributeGroup $attributeGroup): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'alpha_dash'],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', 'in:text,textarea,select,number,boolean,file'],
            'options' => ['nullable', 'string'], // comma separated for select
            'is_variant_option' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['options'] = $data['options'] ?? null
            ? array_map('trim', explode(',', $data['options']))
            : null;

        $attributeGroup->fields()->create($data);

        return back()->with('status', 'Field added.');
    }

    public function destroy(CategoryAttributeGroup $attributeGroup, CategoryAttribute $attribute): RedirectResponse
    {
        abort_unless($attribute->category_attribute_group_id === $attributeGroup->id, 404);
        $attribute->delete();

        return back()->with('status', 'Field removed.');
    }
}
