@extends('layouts.app')

@section('content')
<image-container 
    :image="image"
    :annotations="annotations"
    v-on:annotation="handleNewAnnotation"
></image-container>
@endsection
