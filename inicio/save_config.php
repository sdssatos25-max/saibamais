<?php
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['offer_url'], $data['fake_url'])) {
  file_put_contents('redirect_config.json', json_encode([
    'offer_url' => $data['offer_url'],
    'fake_url' => $data['fake_url']
  ], JSON_PRETTY_PRINT));
  echo 'URLs atualizadas com sucesso!';
} else {
  echo 'Dados inválidos.';
}
?>
