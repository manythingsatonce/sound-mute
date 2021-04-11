# SoundMute

<a href="https://github.com/manythingsatonce/SoundMute/releases"><img alt="release" src="https://img.shields.io/github/v/release/manythingsatonce/SoundMute?include_prereleases"></a>
<a href="https://github.com/manythingsatonce/SoundMute/blob/master/LICENSE"><img alt="license" src="https://img.shields.io/github/license/manythingsatonce/SoundMute"></a>  
<a href="https://github.com/manythingsatonce/SoundMute/issues"><img src="https://img.shields.io/github/issues/manythingsatonce/SoundMute"></a>

**A simple program to mute system sounds written in php 7.4.**

>The program for operation downloads and uses the Nirsoft [SoundVolumeView](https://www.nirsoft.net/utils/sound_volume_view.html) softwarea and a modified [php-shellcommand](https://github.com/mikehaertl/php-shellcommand) library.

## Built With

* [Peachpie](https://www.peachpie.io)

## Getting Started

To get a local copy up and running follow these simple steps.

### Installation

1. Clone the repo.

   ```sh
   git clone https://github.com/manythingsatonce/SoundMute
   ```

2. Install Peachpie .NET Templates.

   ```sh
   dotnet new -i Peachpie.Templates::*
   ```

3. Compile PHP code.

   ```sh
   dotnet publish -c Release -r win-x64 -o app
   ```
   
4. *To start work

   ```sh
   dotnet run
	```

## How to get in touch?

If you have a problem, write to me in a private message or open issue here.
