# Pixel-Ping
A basic WordPress plugin to capture basic Page Views in a privacy-forward way.

## Overview

This is intended as a light-weight pixel tracker. Its not that light-weight in that it requires WordPress but you can't have everything. The image it loads is about 260 bytes in size fr the network request and payload.

It relies on WP's transients and minimizes reads/write to the filesystem.

Aggregate event data is saved in JSON files that are broken down by year and month. Periodically a background function runs to aggregate them into a CSV.

While the original use-case is to track values like a URL, it can be used with anything as they key.

## Usage

This is intended as a light-weight pixel tracker. Its not that light-weight in that it requires WordPress but you can't have everything.

It relies on WP's transients and minimizes reads/write to the filesystem.

Aggregate event data is saved in JSON files that are broken down by year and month. Periodically a background function runs to aggregate them into a CSV.

After activating the plugin, you will not be able to load a small transparent 1x1 PNG image on your domain:

`https://example.com/pixel.png`

This in itself will not do anything.

Add this as the SRC for an IMAGE and append the URL of the page you want to track.

Example:

`https://example.com/pixel.png?u=https://something.example.com/hello.html`

So...

`<img src="https://example.com/pixel.png?u=https://something.example.com/hello.html"/>`

As image is loaded a counter is incremented. This is stored in WordPress's transient cache. It is written to the filesystem after subsequent calls to minimize read/writes. A cron job is scheduled to ensure that counts are not lost of you are experience low traffic.

## Data

Data is saved long-term in JSON files that correspond to the year and month of the event. As data is stored temporarily in cache, these numbers can lag the actual figures by minutes or a day depending on site traffic volume.

Data also includes the referring domain if available.

## Privacy

This does not track, collect, or store any personal information. It accepts a key, trackes the number of calls against it in aggergate, and the referring domain. Thats pretty much it. No last four digits of your SSN required.

