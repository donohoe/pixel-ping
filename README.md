# Pixel-Ping

A lightweight WordPress plugin for capturing basic page views in a privacy-forward way.

## Overview

Pixel-Ping is designed as a minimal pixel tracker. While it does require WordPress, it keeps overhead low. The tracking image is a 1x1 transparent PNG, with the full network request and payload totaling about 260 bytes.

It uses WordPress transients to limit read/write operations, storing aggregate event data in JSON files organized by year and month. A background process periodically aggregates this data into a CSV.

While the primary use case is tracking URLs, any string key can be tracked.

## Usage

After activating the plugin, a transparent 1x1 PNG becomes available at:

`https://example.com/pixel.png`

On its own, this does nothing. To track a page view, embed it as an image and append the target URL or key as a query parameter.

Example:

```html
<img src="https://example.com/pixel.png?u=https://something.example.com/hello.html"/>

Each time the image is loaded, a counter is incremented. Data is first stored in WordPress's transient cache, then periodically written to disk to reduce I/O. A cron job ensures counts are flushed even if traffic is low.

## Data

Long-term data is stored in JSON files, grouped by year and month. Since data is cached temporarily, there may be a delay of several minutes to a day depending on site traffic.

If available, the referring domain is also recorded.

## Privacy

Pixel-Ping does not track, collect, or store personal information. It logs only a key (such as a URL), the number of requests, and the referring domain. No user identifiers, IPs, or personal data are stored. Thats pretty much it. No last four digits of your SSN required.

