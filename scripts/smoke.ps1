[CmdletBinding()]
param(
    [ValidateRange(1, 65535)]
    [int]$Port = 80
)

$ErrorActionPreference = 'Stop'
$baseUri = 'http://127.0.0.1:{0}' -f $Port
$runId = [guid]::NewGuid().ToString('N')
$email = 'smoke-{0}@example.invalid' -f $runId
$fullName = 'Teste Ficticio {0}' -f $runId

# Documento com digito verificador de CPF propositalmente invalido; cartao com prefixo ficticio.
$digits = -join (1..9 | ForEach-Object { Get-Random -Minimum 0 -Maximum 10 })
$sum = 0
for ($i = 0; $i -lt 9; $i++) {
    $sum += [int][string]$digits[$i] * (10 - $i)
}
$checkDigit = 11 - ($sum % 11)
if ($checkDigit -ge 10) { $checkDigit = 0 }
$wrongDigit = ($checkDigit + 1) % 10
$clientDocument = '{0}{1}0' -f $digits, $wrongDigit
$cardNumber = '0000000{0}' -f $digits
$expiration = (Get-Date).AddYears(5)

function Assert-PostCreated {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][hashtable]$Payload
    )

    $uri = '{0}{1}' -f $baseUri, $Path
    try {
        $response = Invoke-WebRequest -Uri $uri -Method Post -ContentType 'application/json' `
            -Body ($Payload | ConvertTo-Json -Compress) -UseBasicParsing -MaximumRedirection 0 `
            -TimeoutSec 15 -ErrorAction Stop
    } catch {
        $status = 'sem resposta HTTP'
        if ($null -ne $_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        throw ('POST {0} falhou ({1}): {2}' -f $Path, $status, $_.Exception.Message)
    }

    if ([int]$response.StatusCode -ne 201) {
        throw ('POST {0}: esperado HTTP 201, recebido HTTP {1}' -f $Path, $response.StatusCode)
    }
    Write-Output ('POST {0}: HTTP 201' -f $Path)
}

Write-Output ('Dados ficticios desta execucao: {0}' -f $email)
Assert-PostCreated -Path '/mkt/leads' -Payload @{
    fullName = $fullName
    email = $email
}
Assert-PostCreated -Path '/financeiro/clients' -Payload @{
    clientDocument = $clientDocument
    cardOwnerFullName = $fullName
    cardNumber = $cardNumber
    cardExpirationMonth = $expiration.ToString('MM')
    cardExpirationYear = $expiration.ToString('yyyy')
    cardSecurityCode = '000'
    email = $email
}

$loginUri = '{0}/academico/login' -f $baseUri
$loginBody = @{ email = $email; senha = '123456' } | ConvertTo-Json -Compress
$deadline = [DateTime]::UtcNow.AddSeconds(45)
$loginSucceeded = $false
$lastLoginError = $null

while ([DateTime]::UtcNow -lt $deadline) {
    $loginResponse = $null
    try {
        $loginResponse = Invoke-WebRequest -Uri $loginUri -Method Post -ContentType 'application/json' `
            -Body $loginBody -UseBasicParsing -MaximumRedirection 0 -TimeoutSec 15 -ErrorAction Stop
        $status = [int]$loginResponse.StatusCode
    } catch {
        if ($null -eq $_.Exception.Response) {
            $status = 0
            $lastLoginError = $_.Exception.Message
        } else {
            $status = [int]$_.Exception.Response.StatusCode
            if ($status -ne 401) {
                throw ('POST /academico/login falhou com HTTP {0}.' -f $status)
            }
        }
    }

    if ($status -eq 200) {
        $jwt = [string]$loginResponse.Content
        if ([string]::IsNullOrWhiteSpace($jwt) -or
            $jwt.Trim() -notmatch '^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$') {
            throw 'POST /academico/login retornou HTTP 200 sem JWT valido de tres segmentos.'
        }
        $loginSucceeded = $true
        Write-Output 'POST /academico/login: HTTP 200 e JWT de tres segmentos.'
        break
    }

    if ($status -ne 401 -and $status -ne 0) {
        throw ('POST /academico/login: esperado HTTP 200 ou 401, recebido HTTP {0}.' -f $status)
    }
    $remainingMs = [int]($deadline - [DateTime]::UtcNow).TotalMilliseconds
    if ($remainingMs -gt 0) {
        Start-Sleep -Milliseconds ([Math]::Min(2000, $remainingMs))
    }
}

if (-not $loginSucceeded) {
    throw ('POST /academico/login nao retornou HTTP 200 em aproximadamente 45 segundos. Ultima falha: {0}' -f $lastLoginError)
}
