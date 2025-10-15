<?php

// Percorso del file di log già creato dall'utente
$logFile = __DIR__ . '/data/log.txt';

// Inizializza variabili per il messaggio finale
$resultMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal form
    $fromScale = isset($_POST['from_scale']) ? trim($_POST['from_scale']) : '';
    $toScale = isset($_POST['to_scale']) ? trim($_POST['to_scale']) : '';
    $rawValue = isset($_POST['temperature_value']) ? trim($_POST['temperature_value']) : '';

    // Convalida scale ammesse
    $validScales = ['celsius', 'fahrenheit', 'kelvin'];
    $isFromValid = in_array($fromScale, $validScales, true);
    $isToValid = in_array($toScale, $validScales, true);

    // Convalida valore numerico
    $value = filter_var($rawValue, FILTER_VALIDATE_FLOAT);

    if (!$isFromValid || !$isToValid) {
        $errorMessage = 'Seleziona una scala di partenza e una scala di arrivo valide.';
    } elseif ($value === false) {
        $errorMessage = 'Inserisci un valore numerico valido.';
    } else {
        // Conversione semplice: normalizza in Celsius
        switch ($fromScale) {
            case 'celsius':
                $celsius = $value;
                break;
            case 'fahrenheit':
                $celsius = ($value - 32) * 5 / 9;
                break;
            case 'kelvin':
                $celsius = $value - 273.15;
                break;
            default:
                $celsius = $value;
        }

        // Converte dalla scala Celsius a quella scelta
        switch ($toScale) {
            case 'celsius':
                $converted = $celsius;
                break;
            case 'fahrenheit':
                $converted = ($celsius * 9 / 5) + 32;
                break;
            case 'kelvin':
                $converted = $celsius + 273.15;
                break;
            default:
                $converted = $celsius;
        }

        // Messaggio da mostrare all'utente
        $resultMessage = "Da {$fromScale} a {$toScale}: {$value} → " . round($converted, 2);

        // Costruisce il log
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logLine = "[LOG] - [{$timestamp}] - {$ipAddress} - From {$fromScale} to {$toScale} - {$value} - " . round($converted, 2);

        file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    if ($errorMessage !== null) {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logLine = "[ERROR] - [{$timestamp}] - {$ipAddress} - {$errorMessage}";
        file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultato conversione</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h1 class="h4 mb-4">Risultato conversione</h1>

                        <?php if ($errorMessage !== null): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php elseif ($resultMessage !== null): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($resultMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Compila il form per effettuare una conversione.</p>
                        <?php endif; ?>

                        <a href="index.html" class="btn btn-primary mt-3">Torna al form</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>