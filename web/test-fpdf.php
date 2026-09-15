<?php
$path = __DIR__ . '/../vendor/fpdf/fpdf.php';

if (file_exists($path)) {
    require_once $path;
    if (class_exists('FPDF')) {
        echo "✅ FPDF instalado correctamente en: $path";
    } else {
        echo "⚠️ El archivo existe pero la clase FPDF no se cargó";
    }
} else {
    echo "❌ Archivo no encontrado en: $path";
}