
@extends('layouts.frontlayout')

@section('title', 'My Prompts - AI Prompt Hub')

@section('content')
    <user-prompts
        :initial-prompts='@json($prompts->getCollection()->values())'
        :current-page="{{ $prompts->currentPage() }}"
        :last-page="{{ $prompts->lastPage() }}"
        :total="{{ $prompts->total() }}"
        :per-page="{{ $prompts->perPage() }}"
        :search='@json(request("search", ""))'
        :success-message='@json(session("success", ""))'
        home-url="{{ route('home') }}"
        prompts-url="{{ route('user.prompts') }}"
        suggestions-url="{{ route('user.prompts.suggestions') }}"
        edit-url-template="{{ url('/my-prompts/__PROMPT_ID__/edit') }}"
        delete-url-template="{{ url('/my-prompts/__PROMPT_ID__') }}"
    ></user-prompts>
@endsection
