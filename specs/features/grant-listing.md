# Feature: Grant Listing (Polda & Mabes)

## Status: REMOVED

This feature was part of the old app but is **not carried over** to the new app.

## Rationale

In the new app, Polda and Mabes access grants through their **review index pages** (`grant-review.index`, `agreement-review.index`, `mabes-grant-review.index`, `mabes-agreement-review.index`). These task-oriented views are the intended way to browse and interact with grants. A separate read-only listing page is unnecessary.

## How Grants Are Accessed Instead

| Actor  | Planning Grants | Agreement Grants |
|--------|-----------------|------------------|
| Polda  | `/grant-review` | `/agreement-review` |
| Mabes  | `/mabes-grant-review` | `/mabes-agreement-review` |
| Satker | `/grant-planning` | `/grant-agreement` |

All users can view full grant details via `/grant-detail/{grant}`.
