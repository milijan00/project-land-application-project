<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    public function index($role, Request $request ) {
        /*
         * URL example: GET http://localhost:8000/articles/Customer?idCategory=1&sort=price:desc
         * */
        try{
            if(!in_array($role, ["Customer", "Administrator"])){
                return response("Forbidden", 403);
            }

            $filter = [];
            $column = "name";
            $sortType = "asc";
            $sortingValues = ["price:asc", "price:desc", "percentage:asc", "percentage:desc"];

            if($request->query("name")){
               $filter[] = ["name", "LIKE", "%" . $request->query("name") . "%"];
            }

            if($request->query("idCategory")){
                $filter[] = ["idCategory", $request->query("idCategory")];
            }

            if($request->query("sort") && in_array($request->query("sort"), $sortingValues)){
               $column = explode(":", $request->query("sort"))[0]  ;
               $sortType = explode(":", $request->query("sort"))[1]  ;
            }

            if($role == "Customer"){
                if(count($filter) > 0)return Article::where("stock", ">", 0)->where($filter)->orderBy($column, $sortType)->get();
                return Article::where("stock", ">", 0)->orderBy($column, $sortType)->get();

            }else if($role == "Administrator"){
                if(count($filter) > 0) return Article::where($filter)->orderBy($column, $sortType)->get();
                return Article::all();
            }else {
               return response("Forbidden", 403);
            }
        }catch(\Exception $e){
            return response("Internal server error", 500);
        }
    }

    public function store($role, Request $request){
        try {
            if ($role != "Administrator") return response("Forbidden", 403);

            $validated = $request->validate([
                "name" => "required|unique:articles|max:100",
                "description" => "required|max:255",
                "image" => ["required", "max:255", "regex:/.*(.jpg|.png)$/"],
                "price" => "required|numeric",
                "percentage" => "required|numeric",
                "stock" => "required|integer",
                "idCategory" => "required|integer"
            ]);

            $article = new Article;
            $article->name = $validated["name"];
            $article->description = $validated["description"];
            $article->price = $validated["price"];
            $article->image = $validated["image"];
            $article->idCategory = $validated["idCategory"];
            $article->percentage = $validated["percentage"];
            $article->stock = $validated["stock"];
            $article->save();
            $created = 201;
            return response("Created", $created);
        }catch(ValidationException $ex){
           return response( $ex->errors(), 422) ;
        }
        catch(\Exception $ex){
            return response("Internal server error", 500);
        }
    }

    public function update($role, $id, Request $request){
        try{
            if(!in_array($role, ["Salesman", "Administrator"])) return response("Forbidden", 403);
            $article = Article::find($id);
            if($article == null){
                return response("There is no such article", 404);
            }
            if($role == "Salesman"){
                if($request->stock){
                    $validated = $request->validate([
                        "stock" => "integer"
                    ]);
                    $article->stock = $validated["stock"];
                }

                if($request->percentage){
                    $validated = $request->validate([
                        "percentage" => "numeric"
                    ]);
                    $article->percentage = $validated["percentage"];
                }
            }else {
                if($request->name){
                    $validated = $request->validate([
                        "name" => "unique:articles|max:100"
                    ]);
                    $article->name = $validated["name"];

                }

                if($request->description){
                    $validated = $request->validate([
                        "description" => "max:255"
                    ]);
                    $article->description = $validated["description"];
                }

                if($request->price){
                    $validated = $request->validate([
                        "price" => "numeric"
                    ]);
                    $article->price = $validated["price"];
                }

                if($request->stock){
                    $validated = $request->validate([
                        "stock" => "integer"
                    ]);
                    $article->stock = $validated["stock"];
                }

                if($request->percentage){
                    $validated = $request->validate([
                        "percentage" => "numeric"
                    ]);
                    $article->percentage = $validated["percentage"];
                }

                if($request->image){
                    $validated = $request->validate([
                        "image" => ["max:255", "regex:/^.*(.jpg|.png)$/"]
                    ]);
                    $article->image = $validated["image"];
                }

                if($request->idCategory){
                    $validated = $request->validate([
                        "idCategory" => "integer"
                    ]);
                    $category = Category::find($validated["idCategory"]);
                    if($category == null){
                        return response("There is no such category", 404);
                    }
                    $article->idCategory = $category->id;
                }
            }

            $article->save();
            return response("No Content", 204);
        }catch(ValidationException $ex){
            return response($ex->errors(), 422);
        }
        catch(\Exception $ex){
            return response("Internal server error", 500);
        }

    }

    public function delete($role, $id){
        try{
            if($role != "Administrator") return response("Forbidden", 403);
            $article = Article::find($id);
            if($article == null){
                return response("There is no such article.", 404);
            }

            $article->delete();
            $noContent = 204;
            return response("Article has been deleted", $noContent);
        }
        catch(\Exception $ex){
            return response("Internal server error", 500);
        }
    }

    public function buyArticle($role, Request $request)
    {
        try {
            if (!in_array($role, ["Customer", "Administrator"])) return response("Forbidden", 403);
            $article = Article::find($request->id);
            if ($article == null) {
                return response("Not found", 404);
            }
            $validated = $request->validate([
                "quantity" => "integer"
            ]);
            if ($article->stock < $validated["quantity"]) {
                return response("Conflict", 409);
            }
            $article->stock = $article->stock - $validated["quantity"];
            $article->save();
            return response("No content", 204);
        }
        catch (ValidationException $ex) {

            return response($ex->errors(), 422);
        }
        catch(\Exception $ex){
            return response("Internal server error", 500);
        }

    }
}
