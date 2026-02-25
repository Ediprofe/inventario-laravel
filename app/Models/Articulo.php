<?php

namespace App\Models;

use App\Enums\CategoriaArticulo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Articulo extends Model
{
    protected $fillable = [
        'nombre',
        'categoria',
        'codigo',
        'descripcion',
        'foto_path',
        'activo',
    ];

    protected $casts = [
        'categoria' => CategoriaArticulo::class,
        'activo' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public static function generateUniqueCodigo(string $categoria): string
    {
        $prefix = static::prefixForCategoria($categoria);
        $pattern = '/^'.preg_quote($prefix, '/').'-(\d{5})$/';
        $maxSequence = 0;

        $codes = static::query()
            ->where('codigo', 'like', $prefix.'-%')
            ->pluck('codigo');

        foreach ($codes as $code) {
            if (preg_match($pattern, (string) $code, $matches)) {
                $sequence = (int) $matches[1];
                if ($sequence > $maxSequence) {
                    $maxSequence = $sequence;
                }
            }
        }

        $nextSequence = $maxSequence + 1;

        do {
            $candidate = sprintf('%s-%05d', $prefix, $nextSequence);
            $exists = static::query()->where('codigo', $candidate)->exists();
            $nextSequence++;
        } while ($exists);

        return $candidate;
    }

    protected static function prefixForCategoria(string $categoria): string
    {
        return match ($categoria) {
            CategoriaArticulo::TECNOLOGIA->value => 'TEC',
            CategoriaArticulo::MOBILIARIO->value => 'MOB',
            CategoriaArticulo::LABORATORIO->value => 'LAB',
            CategoriaArticulo::DEPORTES->value => 'DEP',
            CategoriaArticulo::AUDIOVISUAL->value => 'AUD',
            CategoriaArticulo::LIBROS->value => 'LIB',
            CategoriaArticulo::HERRAMIENTAS->value => 'HER',
            CategoriaArticulo::VEHICULOS->value => 'VEH',
            default => 'OTR',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Articulo $articulo) {
            if (filled($articulo->codigo) || blank($articulo->categoria)) {
                return;
            }

            $categoria = $articulo->categoria instanceof CategoriaArticulo
                ? $articulo->categoria->value
                : (string) $articulo->categoria;

            $articulo->codigo = static::generateUniqueCodigo($categoria);
        });

        static::saved(function (Articulo $articulo) {
            if (blank($articulo->foto_path)) {
                return;
            }

            static::normalizePhotoPath($articulo);
        });
    }

    protected static function normalizePhotoPath(Articulo $articulo): void
    {
        $disk = Storage::disk('public');
        $path = (string) $articulo->foto_path;

        if (! $disk->exists($path)) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($extension !== 'webp') {
                $directory = trim((string) pathinfo($path, PATHINFO_DIRNAME), '.');
                $filename = (string) pathinfo($path, PATHINFO_FILENAME);
                $candidateWebpPath = ($directory !== '' ? $directory.'/' : '').$filename.'.webp';

                if ($disk->exists($candidateWebpPath)) {
                    static::query()->whereKey($articulo->id)->update(['foto_path' => $candidateWebpPath]);
                    $articulo->forceFill(['foto_path' => $candidateWebpPath]);
                }
            }

            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'webp') {
            return;
        }

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'heic', 'heif'], true)) {
            return;
        }

        $sourcePath = $disk->path($path);
        $directory = trim((string) pathinfo($path, PATHINFO_DIRNAME), '.');
        $filename = (string) pathinfo($path, PATHINFO_FILENAME);
        $webpPath = ($directory !== '' ? $directory.'/' : '').$filename.'.webp';
        $targetPath = $disk->path($webpPath);

        $saved = match ($extension) {
            'jpg', 'jpeg', 'png' => static::convertWithGd($sourcePath, $targetPath, $extension),
            'heic', 'heif' => static::convertWithImagick($sourcePath, $targetPath),
            default => false,
        };

        if (! $saved) {
            return;
        }

        if ($webpPath !== $path && $disk->exists($path)) {
            $disk->delete($path);
        }

        if ($articulo->foto_path !== $webpPath) {
            static::query()->whereKey($articulo->id)->update(['foto_path' => $webpPath]);
            $articulo->forceFill(['foto_path' => $webpPath]);
        }
    }

    protected static function convertWithGd(string $sourcePath, string $targetPath, string $extension): bool
    {
        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            default => null,
        };

        if (! $image) {
            return false;
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $saved = @imagewebp($image, $targetPath, 82);
        imagedestroy($image);

        return (bool) $saved;
    }

    protected static function convertWithImagick(string $sourcePath, string $targetPath): bool
    {
        if (! extension_loaded('imagick') || ! class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $imagick = new \Imagick;
            $imagick->readImage($sourcePath.'[0]');
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality(82);
            $imagick->stripImage();
            $saved = $imagick->writeImage($targetPath);
            $imagick->clear();
            $imagick->destroy();

            return (bool) $saved;
        } catch (Throwable) {
            return false;
        }
    }
}
