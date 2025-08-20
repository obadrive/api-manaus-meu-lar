<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gamificacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'gamificacao';

    protected $fillable = [
        'usuario_id',
        'nivel',
        'xp_total',
        'gocoins',
        'avatar_atual',
        'conquistas_desbloqueadas',
        'missoes_ativas',
        'missoes_concluidas',
        'ultima_atualizacao',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'xp_total' => 'integer',
        'gocoins' => 'integer',
        'conquistas_desbloqueadas' => 'array',
        'missoes_ativas' => 'array',
        'missoes_concluidas' => 'array',
        'ultima_atualizacao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function conquistas(): HasMany
    {
        return $this->hasMany(Conquista::class);
    }

    public function missoes(): HasMany
    {
        return $this->hasMany(Missao::class);
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(TransacaoGamificacao::class);
    }

    // Métodos
    public function adicionarXP(int $xp): void
    {
        $this->xp_total += $xp;
        $this->verificarNivel();
        $this->save();
    }

    public function adicionarGoCoins(int $gocoins): void
    {
        $this->gocoins += $gocoins;
        $this->save();
    }

    public function gastarGoCoins(int $gocoins): bool
    {
        if ($this->gocoins >= $gocoins) {
            $this->gocoins -= $gocoins;
            $this->save();
            return true;
        }
        return false;
    }

    public function verificarNivel(): void
    {
        $novoNivel = floor($this->xp_total / 1000) + 1;
        if ($novoNivel > $this->nivel) {
            $this->nivel = $novoNivel;
            // Aqui você pode adicionar lógica para recompensas de nível
        }
    }

    public function desbloquearConquista(string $conquistaId): void
    {
        $conquistas = $this->conquistas_desbloqueadas ?? [];
        if (!in_array($conquistaId, $conquistas)) {
            $conquistas[] = $conquistaId;
            $this->conquistas_desbloqueadas = $conquistas;
            $this->save();
        }
    }

    public function temConquista(string $conquistaId): bool
    {
        $conquistas = $this->conquistas_desbloqueadas ?? [];
        return in_array($conquistaId, $conquistas);
    }

    public function adicionarMissaoAtiva(string $missaoId): void
    {
        $missoes = $this->missoes_ativas ?? [];
        if (!in_array($missaoId, $missoes)) {
            $missoes[] = $missaoId;
            $this->missoes_ativas = $missoes;
            $this->save();
        }
    }

    public function concluirMissao(string $missaoId): void
    {
        $missoesAtivas = $this->missoes_ativas ?? [];
        $missoesConcluidas = $this->missoes_concluidas ?? [];
        
        if (in_array($missaoId, $missoesAtivas)) {
            $missoesAtivas = array_diff($missoesAtivas, [$missaoId]);
            $missoesConcluidas[] = $missaoId;
            
            $this->missoes_ativas = array_values($missoesAtivas);
            $this->missoes_concluidas = $missoesConcluidas;
            $this->save();
        }
    }

    public function getXPProximoNivel(): int
    {
        $xpNecessario = ($this->nivel * 1000);
        return max(0, $xpNecessario - $this->xp_total);
    }

    public function getProgressoNivel(): float
    {
        $xpNivelAtual = (($this->nivel - 1) * 1000);
        $xpNivelProximo = ($this->nivel * 1000);
        $xpNesteNivel = $this->xp_total - $xpNivelAtual;
        $xpNecessario = $xpNivelProximo - $xpNivelAtual;
        
        return ($xpNesteNivel / $xpNecessario) * 100;
    }
}
