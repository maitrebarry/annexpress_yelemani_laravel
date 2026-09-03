<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageContact;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MessageContactController extends Controller
{
    // Comme ActualiteController : contenu rattaché à une compagnie précise, donc hors de
    // portée du super_admin (id_compagnie NULL pour ce rôle).
    private const ROLES_AUTORISES = ['Admin', 'PDG', 'secretaire'];

    public function index()
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $liste = MessageContact::where('id_compagnie', $user->id_compagnie)
            ->orderBy('traite')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.message-contact.index', ['liste' => $liste]);
    }

    public function marquerTraite(int $id): RedirectResponse
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $message = MessageContact::where('id_compagnie', $user->id_compagnie)->find($id);

        if ($message) {
            $message->update(['traite' => ! $message->traite]);
        }

        return redirect()->route('admin.message-contact.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        MessageContact::where('id_compagnie', $user->id_compagnie)->where('id', $id)->delete();

        Flash::set('Message supprimé.', 'success');

        return redirect()->route('admin.message-contact.index');
    }

    private function autoriser(): void
    {
        abort_unless(in_array(Auth::guard('staff')->user()?->droit, self::ROLES_AUTORISES, true), 403);
    }
}
