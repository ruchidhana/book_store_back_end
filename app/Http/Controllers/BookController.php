<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Book_Authors;
use App\Models\User;
use stdClass;
use Auth;
use App\Models\Author;
use App\Models\Book;
use Validator;
use Log;

class BookController extends Controller
{
    function get_all_books(Request $request)
    {
        $books = DB::table('books')
        ->join('book_authors','books.id','=','book_authors.book_id')
        ->join('authors','book_authors.author_id','=','authors.id')
        ->join('users','users.id','=','authors.user_id')
        ->select('books.*')
        ->where('users.is_active','=',1)
        ->where('users.role_id','=',2)
        ->groupBy('books.id')
        ->get();

        //dd($books);
        $books->transform(function ($book, $key) {
            $book_authors=DB::table('users')
            ->join('authors','users.id','=','authors.user_id')
            ->join('book_authors','book_authors.author_id','=','authors.id')
            ->select('users.first_name')
            ->where('book_authors.book_id',$book->id)
            ->get();

            $book_authors->transform(function ($comp) {
                return [
                    $comp->first_name,
                ];
            });
            $book->author=$book_authors;

            return $book;
        });

        return [
            'book_name' => $books
        ];


    }

    function add_book(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isbn' => 'required|string|max:255|unique:books',
            'title' => 'required|string|max:255',
            'description' => 'required|max:1000',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'image' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                "message" => $validator->errors()->first(),
            ]);
        }

        $user = User::where("email", $request->email)->first();
        $author = Author::where("user_id", $user->id)->first();
        $complete_file_name = $request->file('image')->getClientOriginalName();
        $file_name_only = pathinfo($complete_file_name,PATHINFO_FILENAME);
        $extension = $request->file('image')->getClientOriginalExtension();
        $com_pic = str_replace(' ','_',$file_name_only).'-'.rand().'-'.time().'.'.$extension;
        $path = $request->file('image')->storeAs('public/books',$com_pic);

        DB::beginTransaction();
        try {
        $book = Book::create([
            'isbn' => $request->isbn,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'cover_image' => $com_pic
        ]);

        if($book)
        {
            $book_author = Book_Authors::create([
                'book_id' => $book->id,
                'author_id' => $author->id
            ]);

            DB::commit();
            return response()->json([
                "error" => false,
                "message" => "Book Created in successfully!",
            ]);
        }
        else{
            return response()->json([
                "error" => false,
                "message" => "Book Created in Unsuccessfully!",
            ]);
        }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            Log::critical($e->getMessage());
            return response()->json([
                "error" => true,
                "message" => $e->getMessage(),
            ]);
        }

    }

    function search_books(Request $request)
    {

        $search_key = $request->search_key;
        if($request->search_type=="book_title"){
            $books = DB::table('books')
            ->join('book_authors','books.id','=','book_authors.book_id')
            ->join('authors','book_authors.author_id','=','authors.id')
            ->join('users','users.id','=','authors.user_id')
            ->select('books.*')
            ->where('users.is_active','=',1)
            ->where('users.role_id','=',2)
            ->where('books.title','like','%'.$search_key.'%')
            ->groupBy('books.id')
            ->get();

            //dd($books);
            $books->transform(function ($book, $key) {
                $book_authors=DB::table('users')
                ->join('authors','users.id','=','authors.user_id')
                ->join('book_authors','book_authors.author_id','=','authors.id')
                ->select('users.first_name','users.last_name')
                ->where('book_authors.book_id',$book->id)
                ->get();
                $book_authors->transform(function ($comp) {
                    return [
                        $comp->first_name." ".$comp->last_name,
                    ];
                });
                $book->author=$book_authors;

                return $book;
            });

            return [
                'book_name' => $books,
            ];
        }
        else
        {
            $books = DB::table('books')
            ->join('book_authors','books.id','=','book_authors.book_id')
            ->join('authors','book_authors.author_id','=','authors.id')
            ->join('users','users.id','=','authors.user_id')
            ->select('books.*')
            ->where('users.is_active','=',1)
            ->where('users.role_id','=',2)
            ->where('users.first_name','like','%'.$search_key.'%')
            ->groupBy('books.id')
            ->get();

            //dd($books);
            $books->transform(function ($book, $search_key) {
                $book_authors=DB::table('users')
                ->join('authors','users.id','=','authors.user_id')
                ->join('book_authors','book_authors.author_id','=','authors.id')
                ->select('users.first_name','users.last_name')
                //->where('users.first_name','like','%'.$search_key.'%')
                ->where('book_authors.book_id',$book->id)
                ->get();
                $book_authors->transform(function ($comp) {
                    return [
                        $comp->first_name." ".$comp->last_name,
                    ];
                });
                $book->author=$book_authors;

                return $book;
            });

            return [
                'book_name' => $books,
            ];
        }



    }
}
