<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\DokumentasiAlbum;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::published()->upcoming()->with('kategori')->orderBy('tanggal_mulai')->limit(3)->get();

        $featuredAlbums = DokumentasiAlbum::published()->featured()->orderBy('tanggal_album', 'desc')->limit(6)->get();

        return view('public.home', compact('upcomingEvents', 'featuredAlbums'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function events()
    {
        $events = Event::published()->with('kategori')->orderBy('tanggal_mulai', 'desc')->paginate(9);

        return view('public.events', compact('events'));
    }

    public function eventDetail($slug)
    {
        $event = Event::published()
            ->with(['kategori', 'creator'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.event-detail', compact('event'));
    }

    public function gallery()
    {
        $albums = DokumentasiAlbum::published()->with('kategori')->orderBy('tanggal_album', 'desc')->paginate(12);

        return view('public.gallery', compact('albums'));
    }

    public function galleryAlbum($slug)
    {
        $album = DokumentasiAlbum::published()
            ->with(['kategori', 'event', 'photos'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.gallery-album', compact('album'));
    }

    public function contact()
    {
        return view('public.contact');
    }
}
