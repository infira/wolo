<?php

namespace Wolo\Request\Support;


class ServerCollection extends RequestVariableCollection
{
    public function addr(): ?string { return $this->get('SERVER_ADDR'); }

    public function name(): ?string { return $this->get('SERVER_NAME'); }

    public function software(): ?string { return $this->get('SERVER_SOFTWARE'); }

    public function protocol(): ?string { return $this->get('SERVER_PROTOCOL'); }

    public function method(): ?string { return $this->get('REQUEST_METHOD'); }

    public function time(): ?string { return $this->get('REQUEST_TIME'); }

    public function timeFloat(): ?string { return $this->get('REQUEST_TIME_FLOAT'); }

    public function queryString(): ?string { return $this->get('QUERY_STRING'); }

    public function documentRoot(): ?string { return $this->get('DOCUMENT_ROOT'); }

    public function accept(): ?string { return $this->get('HTTP_ACCEPT'); }

    public function acceptCharset(): ?string { return $this->get('HTTP_ACCEPT_CHARSET'); }

    public function acceptEncoding(): ?string { return $this->get('HTTP_ACCEPT_ENCODING'); }

    public function acceptLanguage(): ?string { return $this->get('HTTP_ACCEPT_LANGUAGE'); }

    public function connection(): ?string { return $this->get('HTTP_CONNECTION'); }

    public function host(): ?string { return $this->get('HTTP_HOST'); }

    public function referer(): ?string { return $this->get('HTTP_REFERER'); }

    public function userAgent(): ?string { return $this->get('HTTP_USER_AGENT'); }

    public function https(): ?string { return $this->get('HTTPS'); }

    public function remoteAddr(): ?string { return $this->get('REMOTE_ADDR'); }

    public function remoteHost(): ?string { return $this->get('REMOTE_HOST'); }

    public function remotePort(): ?string { return $this->get('REMOTE_PORT'); }

    public function remoteUser(): ?string { return $this->get('REMOTE_USER'); }

    public function redirectRemote_user(): ?string { return $this->get('REDIRECT_REMOTE_USER'); }

    public function scriptFilename(): ?string { return $this->get('SCRIPT_FILENAME'); }

    public function admin(): ?string { return $this->get('SERVER_ADMIN'); }

    public function oort(): ?string { return $this->get('SERVER_PORT'); }

    public function signature(): ?string { return $this->get('SERVER_SIGNATURE'); }

    public function pathTranslated(): ?string { return $this->get('PATH_TRANSLATED'); }

    public function scriptName(): ?string { return $this->get('SCRIPT_NAME'); }

    public function uri(): ?string { return $this->get('REQUEST_URI'); }

    public function phpAuthDigest(): ?string { return $this->get('PHP_AUTH_DIGEST'); }

    public function phpAuthUser(): ?string { return $this->get('PHP_AUTH_USER'); }

    public function phpAuthPw(): ?string { return $this->get('PHP_AUTH_PW'); }

    public function authType(): ?string { return $this->get('AUTH_TYPE'); }

    public function pathInfo(): ?string { return $this->get('PATH_INFO'); }

    public function origPathInfo(): ?string { return $this->get('ORIG_PATH_INFO'); }
}