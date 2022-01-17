<article id="article" class="c-article c-article--readable-width s-article u-clearfix">
    <!-- Title -->
    @typography(["element" => "h1"])
        {!! $postTitleFiltered !!}
    @endtypography

    <!-- Blog style author signature -->
    @if(!$postTypeDetails->hierarchical && $isBlogStyle)
        @signature([
            'author'            => $signature->name,
            'avatar_size'       => 'sm',
            'avatar'            => $signature->avatar,
            'authorRole'        => $signature->role,
            'link'              => $signature->link,
            'published'         => $signature->published,
            'updated'           => $signature->updated,
            'updatedLabel'      => $publishTranslations->updated,
            'publishedLabel'    => $publishTranslations->publish,
            'classList'         => ['u-margin__y--2']
        ])
        @endsignature
    @endif

    <!-- Feature Image -->
    @if (!empty($featuredImage->src))
		@image([
			'src'=> $featuredImage->src[0],
			'alt' => $featuredImage->alt,
			'classList' => ['c-article__feature-image']
		])
		@endimage
    @endif

	<!-- Content -->
	{!! $postContentFiltered !!}

    <!-- Comments -->
	@includeIf('partials.comments')
	
</article>