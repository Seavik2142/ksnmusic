<template>
  <ScreenBase class="artist-studio-screen" :background-image="overview?.artist.image || undefined">
    <template #header>
      <div class="px-6 pt-6 pb-4 border-b border-k-fg-10 backdrop-blur-md bg-k-bg-primary/40">
        <!-- Loading State -->
        <div v-if="loading && !overview" class="flex items-center gap-4 animate-pulse">
          <div class="w-16 h-16 rounded-2xl bg-k-fg-10" />
          <div class="space-y-2 flex-1">
            <div class="h-4 w-32 bg-k-fg-10 rounded" />
            <div class="h-8 w-64 bg-k-fg-10 rounded" />
          </div>
        </div>

        <!-- Header Content -->
        <div v-else-if="overview" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <!-- Artist Avatar with Change Photo Overlay -->
            <div
              class="relative shrink-0 group cursor-pointer"
              title="Click to change artist photo"
              @click="openEditArtistModal"
            >
              <img
                v-if="overview.artist.image && !artistImageError"
                :src="overview.artist.image"
                :alt="overview.artist.name"
                class="w-16 h-16 md:w-20 md:h-20 rounded-2xl object-cover shadow-lg ring-2 ring-k-fg-10 group-hover:ring-k-highlight transition duration-200"
                @error="artistImageError = true"
              />
              <div
                v-else
                class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-indigo-600 text-white font-black text-2xl md:text-3xl flex items-center justify-center shadow-lg uppercase select-none ring-2 ring-k-fg-10 group-hover:ring-k-highlight transition duration-200"
              >
                {{ overview.artist.name.charAt(0) }}
              </div>

              <!-- Camera hover overlay -->
              <div
                class="absolute inset-0 rounded-2xl bg-black/65 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white transition duration-200 backdrop-blur-[2px]"
              >
                <Icon :icon="faCamera" class="text-base md:text-lg mb-0.5" />
                <span class="text-[9px] font-bold uppercase tracking-wider">Change</span>
              </div>

              <span
                class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-md border-2 border-k-bg-primary group-hover:scale-110 transition duration-200"
                title="Verified Artist Studio"
              >
                <Icon :icon="faCheck" />
              </span>
            </div>

            <!-- Artist Info & Title -->
            <div class="min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-500/15 text-amber-300 border border-amber-500/30"
                >
                  <Icon :icon="faMicrophoneLines" class="text-[9px]" />
                  Artist Studio
                </span>
                <span class="text-xs text-k-fg-50 font-medium">Real-Time Creator Hub</span>
              </div>
              <h1 class="text-2xl md:text-4xl font-black text-k-fg tracking-tight truncate leading-tight">
                {{ overview.artist.name }}
              </h1>
              <p class="text-xs text-k-fg-60 mt-0.5 flex items-center gap-2">
                <span>{{ formatNumber(overview.stats.total_tracks) }} tracks</span>
                <span>•</span>
                <span>{{ formatNumber(overview.stats.total_albums) }} releases</span>
                <span>•</span>
                <span>{{ formatNumber(overview.stats.followers) }} followers</span>
              </p>
            </div>
          </div>

          <!-- Actions & Artist Switcher -->
          <div class="flex flex-wrap items-center gap-2.5 self-start md:self-auto">
            <!-- Artist Switcher (when multiple accessible artists exist) -->
            <div v-if="accessibleArtists.length > 1" class="relative">
              <select
                :value="overview.artist.id"
                class="appearance-none bg-k-fg-5 hover:bg-k-fg-10 text-xs font-medium text-k-fg py-2 pl-3 pr-8 rounded-xl border border-k-fg-10 focus:outline-none focus:ring-1 focus:ring-k-primary transition cursor-pointer"
                title="Switch artist profile"
                @change="switchArtist(($event.target as HTMLSelectElement).value)"
              >
                <option v-for="a in accessibleArtists" :key="a.id" :value="a.id">
                  {{ a.name }}
                </option>
              </select>
              <Icon
                :icon="faChevronDown"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-k-fg-40 pointer-events-none"
              />
            </div>

            <!-- Upload Music Button -->
            <Btn class="btn-upload flex items-center gap-1.5" @click="goToUpload">
              <Icon :icon="faUpload" />
              <span>Upload Music</span>
            </Btn>

            <!-- Change Photo / Edit Artist Button -->
            <Btn variant="ghost" class="flex items-center gap-1.5" title="Change artist photo & name" @click="openEditArtistModal">
              <Icon :icon="faCamera" />
              <span>Change Photo</span>
            </Btn>

            <!-- View Public Profile Button -->
            <Btn variant="ghost" class="flex items-center gap-1.5" @click="goToPublicProfile">
              <Icon :icon="faArrowUpRightFromSquare" />
              <span>Public Profile</span>
            </Btn>

            <!-- Refresh Button -->
            <button
              type="button"
              class="w-9 h-9 rounded-xl flex items-center justify-center text-k-fg-60 hover:text-k-fg hover:bg-k-fg-10 transition border border-k-fg-10 cursor-pointer"
              :class="{ 'opacity-50 pointer-events-none': loading }"
              title="Refresh statistics"
              @click="refreshData"
            >
              <Icon :icon="faRotateRight" :spin="loading" class="text-sm" />
            </button>
          </div>
        </div>
      </div>
    </template>

    <div class="p-6 space-y-6 max-w-7xl mx-auto w-full">
      <!-- Stat Cards Grid -->
      <div v-if="overview" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Monthly Listeners -->
        <div
          class="p-4 md:p-5 rounded-2xl bg-k-fg-5 border border-k-fg-10 backdrop-blur-md relative overflow-hidden transition-all duration-200 hover:border-emerald-500/40 hover:shadow-lg hover:shadow-emerald-500/5 group"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-k-fg-60">Monthly Listeners</span>
            <div
              class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center text-sm group-hover:scale-105 transition-transform"
            >
              <Icon :icon="faUsers" />
            </div>
          </div>
          <div class="text-2xl md:text-3xl font-black text-k-fg tracking-tight tabular-nums">
            {{ formatNumber(overview.stats.monthly_listeners) }}
          </div>
          <p class="text-[11px] text-k-fg-50 mt-1 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
            Unique listeners in past 30 days
          </p>
        </div>

        <!-- Card 2: Total Streams -->
        <div
          class="p-4 md:p-5 rounded-2xl bg-k-fg-5 border border-k-fg-10 backdrop-blur-md relative overflow-hidden transition-all duration-200 hover:border-sky-500/40 hover:shadow-lg hover:shadow-sky-500/5 group"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-k-fg-60">Total Streams</span>
            <div
              class="w-9 h-9 rounded-xl bg-sky-500/15 text-sky-400 border border-sky-500/25 flex items-center justify-center text-sm group-hover:scale-105 transition-transform"
            >
              <Icon :icon="faHeadphones" />
            </div>
          </div>
          <div class="text-2xl md:text-3xl font-black text-k-fg tracking-tight tabular-nums">
            {{ formatNumber(overview.stats.total_streams) }}
          </div>
          <p class="text-[11px] text-k-fg-50 mt-1">All-time track plays across KSN</p>
        </div>

        <!-- Card 3: Followers & Fans -->
        <div
          class="p-4 md:p-5 rounded-2xl bg-k-fg-5 border border-k-fg-10 backdrop-blur-md relative overflow-hidden transition-all duration-200 hover:border-rose-500/40 hover:shadow-lg hover:shadow-rose-500/5 group"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-k-fg-60">Followers &amp; Fans</span>
            <div
              class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-400 border border-rose-500/25 flex items-center justify-center text-sm group-hover:scale-105 transition-transform"
            >
              <Icon :icon="faHeart" />
            </div>
          </div>
          <div class="text-2xl md:text-3xl font-black text-k-fg tracking-tight tabular-nums">
            {{ formatNumber(overview.stats.followers) }}
          </div>
          <p class="text-[11px] text-k-fg-50 mt-1">Users favorited your artist profile</p>
        </div>

        <!-- Card 4: Catalog & Reach -->
        <div
          class="p-4 md:p-5 rounded-2xl bg-k-fg-5 border border-k-fg-10 backdrop-blur-md relative overflow-hidden transition-all duration-200 hover:border-amber-500/40 hover:shadow-lg hover:shadow-amber-500/5 group"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-k-fg-60">Catalog &amp; Reach</span>
            <div
              class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/25 flex items-center justify-center text-sm group-hover:scale-105 transition-transform"
            >
              <Icon :icon="faCompactDisc" />
            </div>
          </div>
          <div class="text-2xl md:text-3xl font-black text-k-fg tracking-tight tabular-nums">
            {{ overview.stats.total_albums }}
            <span class="text-base font-semibold text-k-fg-60">albums</span>
          </div>
          <p class="text-[11px] text-k-fg-50 mt-1">
            Featured in {{ overview.stats.total_playlists }} community playlists
          </p>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div v-if="overview" class="flex border-b border-k-fg-10">
        <nav class="flex gap-2 -mb-px overflow-x-auto [scrollbar-width:none]">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-t-xl transition-all duration-150 flex items-center gap-2 border-b-2 cursor-pointer"
            :class="
              activeTab === tab.id
                ? 'border-k-primary text-k-primary bg-k-fg-5'
                : 'border-transparent text-k-fg-60 hover:text-k-fg hover:bg-k-fg-5'
            "
            @click="activeTab = tab.id"
          >
            <Icon :icon="tab.icon" class="text-xs" />
            {{ tab.label }}
            <span
              v-if="tab.badge !== undefined"
              class="px-1.5 py-0.5 rounded-full text-[10px] tabular-nums"
              :class="activeTab === tab.id ? 'bg-k-primary/20 text-k-primary' : 'bg-k-fg-10 text-k-fg-60'"
            >
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content Area -->
      <div v-if="overview" class="space-y-6">
        <!-- 1. OVERVIEW TAB -->
        <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left Column: Top Songs (7 cols) -->
          <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-base font-bold text-k-fg flex items-center gap-2">
                  <Icon :icon="faFire" class="text-amber-400 text-sm" />
                  Top Performing Songs
                </h2>
                <p class="text-xs text-k-fg-60">Tracks with the highest all-time streams</p>
              </div>
              <button
                v-if="overview.top_songs.length > 5"
                type="button"
                class="text-xs text-k-primary hover:underline font-semibold cursor-pointer"
                @click="activeTab = 'top_songs'"
              >
                View all tracks →
              </button>
            </div>

            <!-- Empty Top Songs -->
            <div
              v-if="overview.top_songs.length === 0"
              class="p-8 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center space-y-3"
            >
              <div
                class="w-12 h-12 rounded-full bg-k-fg-10 text-k-fg-50 flex items-center justify-center mx-auto text-xl"
              >
                <Icon :icon="faMusic" />
              </div>
              <h3 class="font-bold text-sm text-k-fg">No tracks found</h3>
              <p class="text-xs text-k-fg-50 max-w-sm mx-auto">
                Upload your first audio release to start tracking real-time streams and audience analytics.
              </p>
              <Btn class="btn-upload" @click="goToUpload">Upload First Track</Btn>
            </div>

            <!-- Top Songs List -->
            <div v-else class="rounded-2xl bg-k-fg-5 border border-k-fg-10 divide-y divide-k-fg-5 overflow-hidden">
              <div
                v-for="(song, idx) in overview.top_songs.slice(0, 5)"
                :key="song.id"
                class="p-3.5 flex items-center gap-3.5 hover:bg-k-fg-5 transition-colors group"
              >
                <span class="w-6 text-center font-bold text-xs tabular-nums text-k-fg-40 group-hover:text-k-primary">
                  #{{ idx + 1 }}
                </span>

                <div
                  class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-k-fg-10 relative group/thumb cursor-pointer"
                  title="Click to change artwork"
                  @click.stop="openEditAlbumModal(song.album_id)"
                >
                  <img
                    v-if="song.album_cover"
                    :src="song.album_cover"
                    :alt="song.title"
                    class="w-full h-full object-cover"
                    @error="($event.target as HTMLElement).style.display = 'none'"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-k-fg-40 text-xs">
                    <Icon :icon="faMusic" />
                  </div>
                  <div
                    class="absolute inset-0 bg-black/60 opacity-0 group-hover/thumb:opacity-100 flex items-center justify-center text-white transition-opacity duration-150"
                  >
                    <Icon :icon="faCamera" class="text-xs" />
                  </div>
                </div>

                <div class="min-w-0 flex-1">
                  <h4 class="font-semibold text-sm text-k-fg truncate leading-tight">{{ song.title }}</h4>
                  <p class="text-xs text-k-fg-50 truncate mt-0.5">{{ song.album_name }}</p>
                </div>

                <div class="text-right shrink-0">
                  <div class="font-bold text-xs text-k-fg tabular-nums">{{ formatNumber(song.play_count) }} plays</div>
                  <div class="text-[11px] text-k-fg-40 mt-0.5 flex items-center justify-end gap-1">
                    <Icon :icon="faHeart" class="text-[9px] text-rose-400" />
                    <span>{{ song.favorites_count }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ secondsToHis(song.length) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Top Fans & Promotion (5 cols) -->
          <div class="lg:col-span-5 space-y-6">
            <!-- Pitch to Editors / Curators Banner -->
            <div
              class="p-5 rounded-2xl bg-gradient-to-br from-indigo-900/40 via-purple-900/30 to-k-fg-5 border border-indigo-500/30 backdrop-blur-md relative overflow-hidden space-y-3"
            >
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping" />
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">
                  Playlist Pitching &amp; Features
                </span>
              </div>
              <h3 class="text-base font-bold text-k-fg leading-snug">Pitch New Releases to Curators</h3>
              <p class="text-xs text-k-fg-70 leading-relaxed">
                Add rich metadata, cover art, and genre tags to your songs so server editors and community curators can
                easily find and feature your tracks in official playlists.
              </p>
              <div class="pt-1">
                <Btn
                  variant="ghost"
                  class="text-xs border border-indigo-400/30 text-indigo-300 hover:bg-indigo-500/20"
                  @click="goToUpload"
                >
                  Submit New Release
                </Btn>
              </div>
            </div>

            <!-- Top Fans Preview -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-bold text-k-fg flex items-center gap-1.5">
                    <Icon :icon="faHeart" class="text-rose-400 text-xs" />
                    Top Active Fans
                  </h3>
                  <p class="text-[11px] text-k-fg-60">Listeners streaming your tracks the most</p>
                </div>
                <button
                  v-if="overview.top_listeners.length > 3"
                  type="button"
                  class="text-xs text-k-primary hover:underline font-semibold cursor-pointer"
                  @click="activeTab = 'audience'"
                >
                  View all →
                </button>
              </div>

              <div
                v-if="overview.top_listeners.length === 0"
                class="p-5 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center text-xs text-k-fg-50"
              >
                No listener streams recorded yet. Share your music to build your audience!
              </div>

              <div v-else class="rounded-2xl bg-k-fg-5 border border-k-fg-10 divide-y divide-k-fg-5 overflow-hidden">
                <div
                  v-for="(fan, idx) in overview.top_listeners.slice(0, 4)"
                  :key="fan.id"
                  class="p-3 flex items-center gap-3 hover:bg-k-fg-5 transition-colors"
                >
                  <span
                    class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                    :class="
                      idx === 0
                        ? 'bg-amber-400 text-black'
                        : idx === 1
                          ? 'bg-slate-300 text-black'
                          : 'bg-k-fg-10 text-k-fg-60'
                    "
                  >
                    {{ idx + 1 }}
                  </span>

                  <img
                    v-if="fan.avatar"
                    :src="fan.avatar"
                    :alt="fan.name"
                    class="w-8 h-8 rounded-full object-cover shrink-0"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 uppercase"
                  >
                    {{ fan.name.charAt(0) }}
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="font-medium text-xs text-k-fg truncate">{{ fan.name }}</div>
                    <div class="text-[10px] text-k-fg-40">Superfan</div>
                  </div>

                  <div class="text-right text-xs font-bold text-k-fg tabular-nums shrink-0">
                    {{ formatNumber(fan.play_count) }} plays
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. RELEASES TAB -->
        <div v-else-if="activeTab === 'releases'" class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-k-fg">Music Releases (Albums &amp; Singles)</h2>
              <p class="text-xs text-k-fg-60">All albums, EPs, and collections published under your artist profile</p>
            </div>
            <Btn class="btn-upload flex items-center gap-1.5" @click="goToUpload">
              <Icon :icon="faUpload" />
              <span>New Release</span>
            </Btn>
          </div>

          <div
            v-if="overview.releases.length === 0"
            class="p-12 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center space-y-3"
          >
            <div
              class="w-16 h-16 rounded-full bg-k-fg-10 text-k-fg-40 flex items-center justify-center mx-auto text-2xl"
            >
              <Icon :icon="faCompactDisc" />
            </div>
            <h3 class="font-bold text-base text-k-fg">No releases uploaded yet</h3>
            <p class="text-xs text-k-fg-50 max-w-md mx-auto">
              Ready to share your sound? Upload songs with an album title to organize your discography.
            </p>
            <Btn class="btn-upload" @click="goToUpload">Upload First Release</Btn>
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div
              v-for="album in overview.releases"
              :key="album.id"
              class="group p-3 rounded-2xl bg-k-fg-5 border border-k-fg-10 hover:border-k-primary/40 hover:bg-k-fg-10 transition-all duration-200 cursor-pointer flex flex-col"
              @click="goToAlbum(album.id)"
            >
              <div class="aspect-square rounded-xl overflow-hidden bg-k-fg-10 relative mb-3 shadow-md group/cover">
                <img
                  v-if="album.cover"
                  :src="album.cover"
                  :alt="album.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  @error="($event.target as HTMLElement).style.display = 'none'"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-k-fg-30 text-3xl">
                  <Icon :icon="faCompactDisc" />
                </div>
                <div
                  class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/60 backdrop-blur-md text-white border border-white/10"
                >
                  {{ album.year || 'Single' }}
                </div>
                <button
                  type="button"
                  class="absolute bottom-2 right-2 z-10 w-8 h-8 rounded-full bg-black/75 hover:bg-k-highlight text-white flex items-center justify-center transition shadow-lg backdrop-blur-xs opacity-0 group-hover/cover:opacity-100 cursor-pointer"
                  title="Change album artwork"
                  @click.stop="openEditAlbumModal(album.id)"
                >
                  <Icon :icon="faCamera" class="text-xs" />
                </button>
              </div>

              <h4 class="font-bold text-sm text-k-fg truncate group-hover:text-k-primary transition-colors">
                {{ album.name }}
              </h4>
              <p class="text-xs text-k-fg-50 mt-0.5 flex items-center justify-between">
                <span>{{ album.track_count }} tracks</span>
                <span class="tabular-nums font-semibold">{{ formatNumber(album.total_plays) }} plays</span>
              </p>
            </div>
          </div>
        </div>

        <!-- 3. TOP SONGS TAB -->
        <div v-else-if="activeTab === 'top_songs'" class="space-y-4">
          <div>
            <h2 class="text-lg font-bold text-k-fg">Song Performance Leaderboard</h2>
            <p class="text-xs text-k-fg-60">
              Full ranked breakdown of your tracks by stream count and listener engagement
            </p>
          </div>

          <div
            v-if="overview.top_songs.length === 0"
            class="p-12 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center space-y-3"
          >
            <div
              class="w-16 h-16 rounded-full bg-k-fg-10 text-k-fg-40 flex items-center justify-center mx-auto text-2xl"
            >
              <Icon :icon="faMusic" />
            </div>
            <h3 class="font-bold text-base text-k-fg">No songs recorded</h3>
            <p class="text-xs text-k-fg-50 max-w-sm mx-auto">Upload audio files to view detailed track performance.</p>
            <Btn class="btn-upload" @click="goToUpload">Upload Music</Btn>
          </div>

          <div v-else class="rounded-2xl bg-k-fg-5 border border-k-fg-10 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-k-fg-5 border-b border-k-fg-10 text-k-fg-50 uppercase tracking-wider font-semibold">
                  <tr>
                    <th class="py-3 px-4 w-12 text-center">#</th>
                    <th class="py-3 px-4">Title &amp; Release</th>
                    <th class="py-3 px-4 w-40">All-Time Streams</th>
                    <th class="py-3 px-4 w-28 text-center">Favorites</th>
                    <th class="py-3 px-4 w-24 text-right">Length</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-k-fg-5">
                  <tr
                    v-for="(song, idx) in overview.top_songs"
                    :key="song.id"
                    class="hover:bg-k-fg-5 transition-colors group"
                  >
                    <td class="py-3 px-4 text-center font-bold text-k-fg-40 group-hover:text-k-primary">
                      {{ idx + 1 }}
                    </td>
                    <td class="py-3 px-4">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-9 h-9 rounded-lg overflow-hidden shrink-0 bg-k-fg-10 relative group/thumb cursor-pointer"
                          title="Click to change artwork"
                          @click.stop="openEditAlbumModal(song.album_id)"
                        >
                          <img
                            v-if="song.album_cover"
                            :src="song.album_cover"
                            :alt="song.title"
                            class="w-full h-full object-cover"
                            @error="($event.target as HTMLElement).style.display = 'none'"
                          />
                          <div v-else class="w-full h-full flex items-center justify-center text-k-fg-30 text-xs">
                            <Icon :icon="faMusic" />
                          </div>
                          <div
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover/thumb:opacity-100 flex items-center justify-center text-white transition-opacity duration-150"
                          >
                            <Icon :icon="faCamera" class="text-[10px]" />
                          </div>
                        </div>
                        <div class="min-w-0">
                          <div
                            class="font-bold text-sm text-k-fg truncate group-hover:text-k-primary transition-colors"
                          >
                            {{ song.title }}
                          </div>
                          <div class="text-[11px] text-k-fg-50 truncate">{{ song.album_name }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-4">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-k-fg tabular-nums">{{ formatNumber(song.play_count) }}</span>
                        <!-- Relative Stream Bar -->
                        <div class="flex-1 max-w-[100px] h-1.5 rounded-full bg-k-fg-10 overflow-hidden">
                          <div
                            class="h-full bg-gradient-to-r from-sky-400 to-indigo-500 rounded-full"
                            :style="{
                              width: `${Math.max(5, (song.play_count / Math.max(1, overview.top_songs[0]?.play_count || 1)) * 100)}%`,
                            }"
                          />
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <span class="inline-flex items-center gap-1 font-semibold text-rose-400">
                        <Icon :icon="faHeart" class="text-[10px]" />
                        {{ song.favorites_count }}
                      </span>
                    </td>
                    <td class="py-3 px-4 text-right font-mono text-k-fg-50">
                      {{ secondsToHis(song.length) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 4. PLAYLIST TRACKER TAB -->
        <div v-else-if="activeTab === 'playlists'" class="space-y-4">
          <div>
            <h2 class="text-lg font-bold text-k-fg">Playlist Placements &amp; Tracking</h2>
            <p class="text-xs text-k-fg-60">
              Community and official playlists on KSN that currently feature your tracks
            </p>
          </div>

          <div
            v-if="overview.playlist_appearances.length === 0"
            class="p-12 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center space-y-3"
          >
            <div
              class="w-16 h-16 rounded-full bg-k-fg-10 text-k-fg-40 flex items-center justify-center mx-auto text-2xl"
            >
              <Icon :icon="faListUl" />
            </div>
            <h3 class="font-bold text-base text-k-fg">No playlist inclusions yet</h3>
            <p class="text-xs text-k-fg-50 max-w-md mx-auto">
              When users or curators add your tracks to their public or shared playlists, they will automatically appear
              here with placement counts.
            </p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="playlist in overview.playlist_appearances"
              :key="playlist.id"
              class="p-4 rounded-2xl bg-k-fg-5 border border-k-fg-10 hover:border-k-primary/40 hover:bg-k-fg-10 transition-all duration-200 cursor-pointer flex items-center gap-4 group"
              @click="goToPlaylist(playlist.id)"
            >
              <div class="w-14 h-14 rounded-xl overflow-hidden bg-k-fg-10 shrink-0 shadow-md">
                <img
                  v-if="playlist.cover"
                  :src="playlist.cover"
                  :alt="playlist.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-k-fg-40 text-lg">
                  <Icon :icon="faListUl" />
                </div>
              </div>

              <div class="min-w-0 flex-1">
                <h4 class="font-bold text-sm text-k-fg truncate group-hover:text-k-primary transition-colors">
                  {{ playlist.name }}
                </h4>
                <p class="text-xs text-k-fg-50 truncate mt-0.5">By {{ playlist.creator_name }}</p>
                <div class="flex items-center gap-2 mt-1.5">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25"
                  >
                    {{ playlist.artist_tracks_count }} of your tracks
                  </span>
                  <span class="text-[11px] text-k-fg-40">{{ playlist.songs_count }} total tracks</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. AUDIENCE & FANS TAB -->
        <div v-else-if="activeTab === 'audience'" class="space-y-4">
          <div>
            <h2 class="text-lg font-bold text-k-fg">Audience &amp; Top Fans Leaderboard</h2>
            <p class="text-xs text-k-fg-60">Your most dedicated listeners ranked by overall stream volume</p>
          </div>

          <div
            v-if="overview.top_listeners.length === 0"
            class="p-12 rounded-2xl bg-k-fg-5 border border-k-fg-10 text-center space-y-3"
          >
            <div
              class="w-16 h-16 rounded-full bg-k-fg-10 text-k-fg-40 flex items-center justify-center mx-auto text-2xl"
            >
              <Icon :icon="faUsers" />
            </div>
            <h3 class="font-bold text-base text-k-fg">No audience stream data yet</h3>
            <p class="text-xs text-k-fg-50 max-w-sm mx-auto">
              As listeners stream your releases, their engagement stats will display here.
            </p>
          </div>

          <div v-else class="rounded-2xl bg-k-fg-5 border border-k-fg-10 divide-y divide-k-fg-5 overflow-hidden">
            <div
              v-for="(fan, idx) in overview.top_listeners"
              :key="fan.id"
              class="p-4 flex items-center gap-4 hover:bg-k-fg-5 transition-colors"
            >
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0"
                :class="
                  idx === 0
                    ? 'bg-amber-400 text-black shadow-md shadow-amber-400/20'
                    : idx === 1
                      ? 'bg-slate-300 text-black shadow-md'
                      : idx === 2
                        ? 'bg-amber-700 text-white'
                        : 'bg-k-fg-10 text-k-fg-60'
                "
              >
                {{ idx + 1 }}
              </div>

              <img
                v-if="fan.avatar"
                :src="fan.avatar"
                :alt="fan.name"
                class="w-11 h-11 rounded-full object-cover shrink-0 ring-2 ring-k-fg-10"
              />
              <div
                v-else
                class="w-11 h-11 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-bold text-base flex items-center justify-center shrink-0 uppercase shadow-sm"
              >
                {{ fan.name.charAt(0) }}
              </div>

              <div class="min-w-0 flex-1">
                <div class="font-bold text-sm text-k-fg truncate">{{ fan.name }}</div>
                <div class="text-xs text-k-fg-50 flex items-center gap-2 mt-0.5">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="
                      idx === 0
                        ? 'bg-amber-500/20 text-amber-300'
                        : idx < 3
                          ? 'bg-indigo-500/20 text-indigo-300'
                          : 'bg-k-fg-10 text-k-fg-60'
                    "
                  >
                    {{ idx === 0 ? 'Top Listener #1' : idx < 3 ? 'Top Supporter' : 'Active Listener' }}
                  </span>
                </div>
              </div>

              <div class="text-right shrink-0">
                <div class="font-black text-sm text-k-fg tabular-nums">{{ formatNumber(fan.play_count) }} plays</div>
                <div class="text-[11px] text-k-fg-40 mt-0.5">Total streams</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ScreenBase>
</template>

<script lang="ts" setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import {
  faArrowUpRightFromSquare,
  faCamera,
  faCheck,
  faChevronDown,
  faCompactDisc,
  faFire,
  faHeadphones,
  faHeart,
  faListUl,
  faMicrophoneLines,
  faMusic,
  faRotateRight,
  faUpload,
  faUsers,
} from '@fortawesome/free-solid-svg-icons'
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core'
import type { ArtistStudioArtistInfo, ArtistStudioOverview } from '@/services/artistStudioService'
import { artistStudioService } from '@/services/artistStudioService'
import { artistStore } from '@/stores/artistStore'
import { albumStore } from '@/stores/albumStore'
import { useRouter } from '@/composables/useRouter'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { useModal } from '@/composables/useModal'
import { defineAsyncComponent } from '@/utils/helpers'
import { secondsToHis } from '@/utils/formatters'
import { eventBus } from '@/utils/eventBus'

import ScreenBase from '@/components/screens/ScreenBase.vue'
import Btn from '@/components/ui/form/Btn.vue'

const EditArtistForm = defineAsyncComponent(() => import('@/components/artist/EditArtistForm.vue'))
const EditAlbumForm = defineAsyncComponent(() => import('@/components/album/EditAlbumForm.vue'))

type TabId = 'overview' | 'releases' | 'top_songs' | 'playlists' | 'audience'

interface TabItem {
  id: TabId
  label: string
  icon: IconDefinition
  badge?: number
}

const { go, url, onScreenActivated } = useRouter()
const { toastError } = useMessageToaster()
const { openModal } = useModal()

const loading = ref(false)
const artistImageError = ref(false)
const overview = ref<ArtistStudioOverview | null>(null)
const accessibleArtists = ref<ArtistStudioArtistInfo[]>([])
const activeTab = ref<TabId>('overview')

const formatNumber = (num: number = 0): string => {
  return new Intl.NumberFormat().format(num)
}

const tabs = computed<TabItem[]>(() => {
  if (!overview.value) {
    return []
  }

  return [
    { id: 'overview', label: 'Overview', icon: faMicrophoneLines },
    { id: 'releases', label: 'Releases', icon: faCompactDisc, badge: overview.value.releases.length },
    { id: 'top_songs', label: 'Top Songs', icon: faFire, badge: overview.value.top_songs.length },
    { id: 'playlists', label: 'Playlist Tracker', icon: faListUl, badge: overview.value.playlist_appearances.length },
    { id: 'audience', label: 'Audience & Fans', icon: faUsers, badge: overview.value.top_listeners.length },
  ]
})

const fetchStudioData = async (artistId?: string) => {
  loading.value = true

  try {
    const [overviewData, artistsList] = await Promise.all([
      artistStudioService.fetchOverview(artistId),
      artistStudioService.fetchAccessibleArtists(),
    ])

    overview.value = overviewData
    accessibleArtists.value = artistsList
  } catch (error) {
    toastError('Unable to load Artist Studio data.')
  } finally {
    loading.value = false
  }
}

const switchArtist = async (artistId: string) => {
  await fetchStudioData(artistId)
}

const refreshData = async () => {
  await fetchStudioData(overview.value?.artist.id)
}

const goToUpload = () => go(url('upload'))
const goToPublicProfile = () => {
  if (overview.value) {
    go(url('artists.show', { id: overview.value.artist.id }))
  }
}

const goToAlbum = (albumId: string) => {
  go(url('albums.show', { id: albumId }))
}

const goToPlaylist = (playlistId: string) => {
  go(url('playlists.show', { id: playlistId }))
}

const openEditArtistModal = async () => {
  if (!overview.value?.artist) {
    return
  }

  const artist = artistStore.byId(overview.value.artist.id) ?? (await artistStore.resolve(overview.value.artist.id))

  if (!artist) {
    toastError('Artist not found.')
    return
  }

  openModal<'EDIT_ARTIST_FORM'>(EditArtistForm, {
    artist,
  })
}

const openEditAlbumModal = async (albumId?: string | null) => {
  if (!albumId) {
    return
  }

  const album = albumStore.byId(albumId) ?? (await albumStore.resolve(albumId))

  if (!album) {
    toastError('Album not found.')
    return
  }

  openModal<'EDIT_ALBUM_FORM'>(EditAlbumForm, {
    album,
  })
}

watch(
  () => (overview.value?.artist ? artistStore.byId(overview.value.artist.id)?.image : null),
  newImage => {
    if (newImage && overview.value?.artist) {
      overview.value.artist.image = newImage
      artistImageError.value = false
    }
  },
)

watch(
  () => (overview.value?.artist ? artistStore.byId(overview.value.artist.id)?.name : null),
  newName => {
    if (newName && overview.value?.artist) {
      overview.value.artist.name = newName
    }
  },
)

const onDataChanged = () => {
  refreshData()
}

onScreenActivated('ArtistStudio', () => {
  fetchStudioData(overview.value?.artist.id)
})

onMounted(async () => {
  await fetchStudioData()
  eventBus.on('SONGS_UPDATED', onDataChanged)
  eventBus.on('SONG_UPLOADED', onDataChanged)
  eventBus.on('SONGS_DELETED', onDataChanged)
})

onBeforeUnmount(() => {
  eventBus.off('SONGS_UPDATED', onDataChanged)
  eventBus.off('SONG_UPLOADED', onDataChanged)
  eventBus.off('SONGS_DELETED', onDataChanged)
})
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.artist-studio-screen {
  @apply relative;
}
</style>
