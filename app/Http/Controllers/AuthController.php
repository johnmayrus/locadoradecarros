<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class AuthController extends Controller
    {
        public function login(Request $request)
        {
            $credenciais = $request->all(['email', 'password']);
            //Autenticação email e senha
            $token = auth('api')->attempt($credenciais);

            if ($token) { //Usuário autenticado com sucesso.
                return response()->json(['token' => $token]);
            } else { //Erro de usuário.
                return response()->json(['erro' => 'Usuário ou Senha Inválidos!'], 403);
            }
            //Retorna um Jason Web Token
            return 'login';
        }

        public function logout()
        {
            auth('api')->logout();
            return response()->json(['msg' => 'O logout foi realizado com sucesso!']);
        }

        public function refresh()
        {
            $token = auth('api')->refresh();
            return response()->json(['token' => $token]);
        }

        public function me()
        {
            return response()->json(auth()->user());
        }
    }
