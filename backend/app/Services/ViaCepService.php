<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Cache;

class ViaCepService
{
    protected $client;
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.viacep.base_url', 'https://viacep.com.br/ws');
        $this->timeout = config('services.viacep.timeout', 5000) / 1000;
        
        $this->client = new Client([
            'timeout' => $this->timeout,
            'connect_timeout' => $this->timeout,
        ]);
    }

    public function buscarEndereco(string $cep)
    {
        $cep = preg_replace('/\D/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return [
                'success' => false,
                'message' => 'CEP deve conter 8 dígitos',
                'data' => null,
            ];
        }

        $cacheKey = "viacep:{$cep}";
        
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return [
                'success' => true,
                'message' => 'Endereço encontrado (cache)',
                'data' => $cached,
            ];
        }

        try {
            $response = $this->client->get("{$this->baseUrl}/{$cep}/json/");
            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['erro']) && $data['erro'] === true) {
                return [
                    'success' => false,
                    'message' => 'CEP não encontrado',
                    'data' => null,
                ];
            }

            $endereco = [
                'cep' => preg_replace('/\D/', '', $data['cep']),
                'logradouro' => $data['logradouro'] ?? '',
                'complemento' => $data['complemento'] ?? '',
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['localidade'] ?? '',
                'uf' => $data['uf'] ?? '',
            ];

            Cache::put($cacheKey, $endereco, 86400);

            return [
                'success' => true,
                'message' => 'Endereço encontrado',
                'data' => $endereco,
            ];

        } catch (ConnectException $e) {
            return [
                'success' => false,
                'message' => 'Serviço de CEP indisponível. Por favor, preencha o endereço manualmente.',
                'data' => null,
                'timeout' => true,
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'Erro ao consultar CEP. Por favor, preencha o endereço manualmente.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro inesperado. Por favor, preencha o endereço manualmente.',
                'data' => null,
            ];
        }
    }
}
