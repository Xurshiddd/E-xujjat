<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(15);
        return Inertia::render('Admin/Category', ['categories'=> $categories]);
    }
    public function store(Request $request)
    {
        $category = Category::create($request->all());
        return redirect()->route('categories.index')->with('success','save category');
    }
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return redirect()->route('categories.index')->with('success','update category');
    }
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect()->route('categories.index')->with('success','delete category');
    }
}
