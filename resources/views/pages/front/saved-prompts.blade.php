
@extends('layouts.frontlayout')

@php
    $hideNavbar = true;
@endphp

@section('title', 'Saved Prompts')

@section('content')
    <saved-prompts
        :initial-prompts='@json(
            $prompts->getCollection()->values()
        )'
        :current-page="{{ $prompts->currentPage() }}"
        :last-page="{{ $prompts->lastPage() }}"
        :total="{{ $prompts->total() }}"
        explore-url="{{ route('home') }}"
        save-url-template="{{ url('/prompts/__PROMPT_ID__/save') }}"
    ></saved-prompts>
@endsection
