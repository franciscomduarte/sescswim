@extends('layouts.app')

@section('title', 'Painel - ' . $campeonato->nome)

@section('content')
    <livewire:painel-lancamento :campeonato="$campeonato" />
@endsection
