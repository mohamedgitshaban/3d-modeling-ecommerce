<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAttributeGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryAttributeGroupController extends Controller
{
    public function store(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'alpha_dash'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:richtext,bullet_list,key_value,file_list,badge_list'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category->attributeGroups()->create($data);

        return back()->with('status', 'Section added.');
    }

    public function destroy(Category $category, CategoryAttributeGroup $attributeGroup): RedirectResponse
    {
        abort_unless($attributeGroup->category_id === $category->id, 404);
        $attributeGroup->delete();

        return back()->with('status', 'Section removed.');
    }
}
