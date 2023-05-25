<?php

    namespace App\Http\Controllers;

    use App\Repositories\ModeloRepository;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Storage;
    use App\Models\Modelo;
    use Illuminate\Http\Request;

    class ModeloController extends Controller
    {
        public function __construct(Modelo $modelo)
        {
            $this->modelo = $modelo;
        }

        /**
         * Display a listing of the resource.
         *
         * @param Request $request
         * @return Response
         */
        public function index(Request $request)
        {

            $modeloRepository = new ModeloRepository($this->modelo);

            if ($request->has('atributos_marca')) {
                $atributos_marca = 'marca:id,' . $request->atributos_marca;
                $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
            } else {
                $modeloRepository->selectAtributosRegistrosRelacionados('marca');
            }
            if ($request->has('filtro')) {
                $modeloRepository->filtro($request->filtro);

            }
            if ($request->has('atributos')) {
                $modeloRepository->selectAtributos($request->atributos);
            }

            return response()->json($modeloRepository->getResultado(), 200);
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return void
         */
        public function create()
        {
            //
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param Request $request
         * @return Response
         */
        public function store(Request $request)
        {
            $request->validate($this->marca->rules(), $this->marca->feedback());
            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('imagens', 'public');
            $marca = $this->marca->create([
                'nome' => $request->nome,
                'imagem' => $imagem_urn
            ]);
            return response()->json($marca, 201);
        }


        /**
         * Display the specified resource.
         *
         * @param $id
         * @return Response
         */
        public function show($id)
        {
            $modelo = $this->modelo->with('marca')->find($id);
            if ($modelo === null) {
                return response()->json(['erro' => 'Recurso pesquisado não existe'], 404);
            }

            return response()->json($modelo, 200);
        }

        /**
         * Show the form for editing the specified resource.
         *
         * @param Modelo $modelo
         * @return void
         */
        public function edit(Modelo $modelo)
        {
            //
        }

        /**
         * Update the specified resource in storage.
         *
         * @param Request $request
         * @param $id
         * @return Response
         */
        public function update(Request $request, $id)
        {
            $modelo = $this->modelo->find($id);

            if ($modelo === null) {
                return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'],
                    404);
            }

            if ($request->method() === 'PATCH') {

                $regrasDinamicas = array();

                //percorrendo todas as regras definidas no Model
                foreach ($modelo->rules() as $input => $regra) {

                    //coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                    if (array_key_exists($input, $request->all())) {
                        $regrasDinamicas[$input] = $regra;
                    }
                }

                $request->validate($regrasDinamicas);

            } else {
                $request->validate($modelo->rules());
            }
            //remove o arquivo artigo caso um novo arquivo seja enviado no request
            if ($request->file('imagem')) {
                Storage::disk('public')->delete($modelo->imagem);
            }
            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('imagens/modelos', 'public');
            $modelo->fill($request->all());
            $modelo->imagem = $imagem_urn;
            $modelo->save();
            /*
            $modelo->update([
                'marca_id' => $request->marca_id,
                'nome' => $request->nome,
                'imagem' => $imagem_urn,
                'numero_portas' => $request->numero_portas,
                'lugares' => $request->lugares,
                'air_bag' => $request->air_bag,
                'abs' => $request->abs
            ]);*/
            return response()->json($modelo, 200);
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param $id
         * @return Response
         */
        public function destroy($id)
        {
            $modelo = $this->modelo->find($id);

            if ($modelo === null) {
                return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'],
                    404);
            }
            //remove o arquivo artigo caso um novo arquivo seja enviado no request

            Storage::disk('public')->delete($modelo->imagem);

            $modelo->delete();
            return response()->json(['msg' => 'O modelo foi removido com sucesso!'], 200);
        }
    }
