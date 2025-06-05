<?php

namespace ERPClean\Common;

use Exception;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API ERPClean Easy
 *
 * @category  ERPClean
 * @package   ERPClean\Common\Tools
 * @author    Diego Almeida <diego.feres82 at gmail dot com>
 * @author    Call Seven <call.seven at hotmail dot com>
 * @copyright 2023 ERPClean
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * URL base para comunicação com a API
     *
     * @var string
     */
    public static $API_URL = [
        1 => 'https://api.fuganholi-easy.com.br/api',
        2 => 'http://api.nfservice.com.br/api',
        3 => 'https://api.sandbox.fuganholi-easy.com.br/api',
        4 => 'https://api.dusk.fuganholi-easy.com.br/api'
    ];

    /**
     * Variável responsável por armazenar os dados a serem utilizados para comunicação com a API
     * Dados como token, ambiente(produção ou homologação) e debug(true|false)
     *
     * @var array
     */
    private $config = [
        'token' => '',
        'user_token' => '',
        'environment' => '',
        'debug' => false,
        'upload' => false,
        'decode' => true
    ];

    /**
     * Define se a classe realizará um upload
     *
     * @param bool $isUpload Boleano para definir se é upload ou não
     *
     * @access public
     * @return void
     */
    public function setUpload(bool $isUpload) :void
    {
        $this->config['upload'] = $isUpload;
    }

    /**
     * Define se a classe realizará o decode do retorno
     *
     * @param bool $decode Boleano para definir se fa decode ou não
     *
     * @access public
     * @return void
     */
    public function setDecode(bool $decode) :void
    {
        $this->config['decode'] = $decode;
    }

    /**
     * Função responsável por definir se está em modo de debug ou não a comunicação com a API
     * Utilizado para pegar informações da requisição
     *
     * @param bool $isDebug Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setDebug(bool $isDebug) :void
    {
        $this->config['debug'] = $isDebug;
    }

    /**
     * Função responsável por definir o token a ser utilizado para comunicação com a API
     *
     * @param string $token Token para autenticação na API
     *
     * @access public
     * @return void
     */
    public function setToken(string $token) :void
    {
        $this->config['token'] = $token;
    }

    /**
     * Função responsável por definir o token de usuário a ser utilizado para comunicação com a API
     *
     * @param string $token Token para autenticação na API
     *
     * @access public
     * @return void
     */
    public function setUserToken(string $token) :void
    {
        $this->config['user_token'] = $token;
    }

    /**
     * Função responsável por setar o ambiente utilizado na API
     *
     * @param int $environment Ambiente API (1 - Produção | 2 - Local | 3 - Sandbox | 4 - Dusk)
     *
     * @access public
     * @return void
     */
    public function setEnvironment(int $environment) :void
    {
        if (in_array($environment, [1, 2, 3, 4])) {
            $this->config['environment'] = $environment;
        }
    }

    /**
     * Recupera se é upload ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getUpload() : bool
    {
        return $this->config['upload'];
    }

    /**
     * Recupera se faz decode ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getDecode() : bool
    {
        return $this->config['decode'];
    }

    /**
     * Retorna o token utilizado para comunicação com a API
     *
     * @access public
     * @return string
     */
    public function getToken() :string
    {
        return $this->config['token'];
    }

    /**
     * Retorna o token de usuário utilizado para comunicação com a API
     *
     * @access public
     * @return string
     */
    public function getUserToken() :string
    {
        return $this->config['user_token'];
    }

    /**
     * Recupera o ambiente setado para comunicação com a API
     *
     * @access public
     * @return int
     */
    public function getEnvironment() :int
    {
        return $this->config['environment'];
    }

    /**
     * Retorna os cabeçalhos padrão para comunicação com a API
     *
     * @access private
     * @return array
     */
    private function getDefaultHeaders() :array
    {
        $headers = [
            'access-token: '.$this->config['token'],
            'Authorization: Bearer '.$this->config['user_token'],
            'Accept: application/json',
        ];

        if (!$this->config['upload']) {
            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Type: multipart/form-data';
        }
        return $headers;
    }

    /**
     * Cadastra uma empresa e seus usuários
     *
     * @access public
     * @return array
     */
    public function cadastraEmpresa(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("systems/companies", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Atualiza as configurações de uma empresa
     *
     * @access public
     * @return array
     */
    public function atualizaConfigEmpresa(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("companies/settings", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as situações tributárias de ICMS
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaSitTribIcms(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/situacao-tributaria-icms", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as situações tributárias de IPI
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaSitTribIpi(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/situacao-tributaria-ipi", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as situações tributárias de PIS
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaSitTribPis(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/situacao-tributaria-pis", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as situações tributárias de COFINS
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaSitTribCofins(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/situacao-tributaria-cofins", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as modalidades de ICMS
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaModIcms(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/modalidade-icms", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as modalidades de ICMS-ST
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaModIcmsSt(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/modalidade-icms-st", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar os motivos de desoneracao ICMS
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaMotivoDesonIcms(array $params = []) :array
    {
        try {
            $dados = $this->get("cfops/motivos-desoneracao", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar uma CFOP
     *
     * @param array $dados Dados da CFOP
     *
     * @access public
     * @return array
     */
    public function cadastraCfop(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post("cfops", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar uma NCM
     *
     * @param array $dados Dados da NCM
     *
     * @access public
     * @return array
     */
    public function cadastraNcm(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post("ncms", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar uma CEST
     *
     * @param array $dados Dados da CEST
     *
     * @access public
     * @return array
     */
    public function cadastraCest(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post("cests", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar os tipos de unidades de produtos
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaUnidades(array $params = []) :array
    {
        try {
            $dados = $this->get("product-unit", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as origens de produtos
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaOrigens(array $params = []) :array
    {
        try {
            $dados = $this->get("product-origin", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar os tipos de produtos
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaTipos(array $params = []) :array
    {
        try {
            $dados = $this->get("product-type", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar os tipos específicos de produtos
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaTiposEspecificos(array $params = []) :array
    {
        try {
            $dados = $this->get("product-specific-types", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as contas
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaContas(array $params = []) :array
    {
        try {
            $dados = $this->get("installments/accountant", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }


    /**
     * Função responsável por listar as contas com baixas de notas fiscais
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaContasComBaixasDeNotasFiscais(array $params = []) :array
    {
        try {
            $dados = $this->get("invoices-payments", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }


    /**
     * Função responsável por listar as categorias
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaCategorias(array $params = []) :array
    {
        try {
            $dados = $this->get("categories/select", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as pessoas
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaPessoas(array $params = []) :array
    {
        try {
            $dados = $this->get("persons", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as contas bancárias
     *
     * @param array $params Parametros adicionais para a busca
     *
     * @access public
     * @return array
     */
    public function buscaContasBancarias(array $params = []) :array
    {
        try {
            $dados = $this->get("accounts", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar um produto
     *
     * @param array $dados Dados do produto
     *
     * @access public
     * @return array
     */
    public function cadastraProduto(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post("products", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra uma conta bancária
     *
     * @access public
     * @return array
     */
    public function cadastraContaBancaria(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("accounts", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra uma categoria financeira
     *
     * @access public
     * @return array
     */
    public function cadastraCategoria(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("categories", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra uma pessoa
     *
     * @access public
     * @return array
     */
    public function cadastraPessoa(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("persons", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra contas a pagar/receber
     *
     * @access public
     * @return array
     */
    public function cadastraCobranca(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("systems/installments", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra uma serie fiscal
     *
     * @access public
     * @return array
     */
    public function cadastraSerieFiscal(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("series", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra vendas e orçamentos
     *
     * @access public
     * @return array
     */
    public function cadastraVenda(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("systems/orders", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra compras
     *
     * @access public
     * @return array
     */
    public function cadastraCompra(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post("purchases", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Importa os XMLs de NFes
     *
     * @param array $dados Dados para a importação
     *
     * @access public
     * @return array
     */
    public function importaXmlNFe(array $dados, array $params = []): array
    {
        if (!isset($dados['xmls']) || empty($dados['xmls'])) {
            throw new \Exception("Informe os XMLs a serem importadas", 1);
        }

        try {
            $this->setUpload(true);
            $dados = $this->post("invoices/import", $dados, $params);
            $this->setUpload(false);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
    * Função responsável por consultar se já existe empresa cadastrada no sistema
    *
    * @access public
    * @return array
    */
    public function consultaEmpresaCnpj(string $cnpj, array $params = []): array
    {

        $params = array_filter($params, function($item) {
            return $item['name'] !== 'cnpj';
        }, ARRAY_FILTER_USE_BOTH);

        $params[] = [
            'name' => 'cnpj',
            'value' => $cnpj
        ];

        try {
            $dados = $this->get("companies/verifycompanyexist", $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
    * Função responsável por consultar logo e trazer a url
    *
    * @access public
    * @param string $cnpj CNPJ a ser consultado
    * @param array $params Parametros adicionais para a consulta
    * @return string or null
    */
    public function consultaLogo(string $cnpj, array $params = [])
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'cnpj';
            }, ARRAY_FILTER_USE_BOTH);


            $params[] = [
                'name' => 'cnpj',
                'value' => $cnpj
            ];
            $dados = $this->get("companies/verifycompanyexist", $params);

            if ($dados['httpCode'] == 200) {

                $this->config['decode'] = false;

                $company_id = $dados['body']->id;

                $logo = $this->get("companies/$company_id/logo");

                if ($logo['httpCode'] == 200) {

                    return $logo['body'];
                } else {
                    return null;
                }

            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar um e-mail no sistema
     *
     * @param string $email E-mail a ser consultado
     * @param array $params parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaEmail(string $email, array $params = []) :array
    {
        try {
            $dados = [
                'email' => $email
            ];

            $dados = $this->post("companies/verify-email", $dados, $params);

            return $dados;
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por registrar uma empresa no teste grátis
     *
     * @param array $dados Dados para cadastro da empresa
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function cadastraTesteGratis(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post("register", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

     /**
     * Função responsável por retornar o token de acesso do contador
     *
     * @param array $dados Dados do cadastro do contador e empresa
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function cadastraTokenContador(array $dados, array $params = []) :array
    {
        try {

            if(!isset($dados['name']) || empty($dados['name'])){
                throw new Exception("Informe o nome do contador", 1);
            }
            if(!isset($dados['email']) || empty($dados['email'])){
                throw new Exception("Informe o e-mail do contador", 1);
            }
            if(!isset($dados['cpfcnpj']) || empty($dados['cpfcnpj'])){
                throw new Exception("Informe o CPF ou CNPJ do contador", 1);
            }
            if(!isset($dados['phone']) || empty($dados['phone'])){
                throw new Exception("Informe o telefone do contador", 1);
            }
            if(!isset($dados['name_company']) || empty($dados['name_company'])){
                throw new Exception("Informe o nome da empresa do contador", 1);
            }
            if(!isset($dados['customer_cpfcnpj']) || empty($dados['customer_cpfcnpj'])){
                throw new Exception("Informe o CPF ou CNPJ da empresa que o contador irá acessar", 1);
            }

            $dados = $this->post("nfcontador/access-customer", $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $this->convertToFormData($body),
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function delete(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access protected
     * @return array
     */
    protected function execute(string $path, array $opts = [], array $params = []) :array
    {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $url = self::$API_URL[$this->config['environment']].$path;

        $curlC = curl_init();

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && (!empty($param['value']) || $param['value'] == 0)) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        if (!empty($dados)) {
            curl_setopt($curlC, CURLOPT_POSTFIELDS, json_encode($dados));
        }
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $return["body"] = ($this->config['decode'] || !$this->config['decode'] && $info['http_code'] != '200') ? json_decode($retorno) : $retorno;
        $return["httpCode"] = curl_getinfo($curlC, CURLINFO_HTTP_CODE);
        if ($this->config['debug']) {
            $return['info'] = curl_getinfo($curlC);
        }
        curl_close($curlC);

        return $return;
    }

    /**
     * Função responsável por montar o corpo de uma requisição no formato aceito pelo FormData
     */
    private function convertToFormData($data)
    {
        $dados = [];

        $recursive = false;
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $dados[$key] = $value;
            } else {
                foreach ($value as $subkey => $subvalue) {
                    $dados[$key.'['.$subkey.']'] = $subvalue;

                    if (is_array($subvalue)) {
                        $recursive = true;
                    }
                }
            }
        }

        if ($recursive) {
            return $this->convertToFormData($dados);
        }

        return $dados;
    }
}
