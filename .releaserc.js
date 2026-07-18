// Forge gate: GITEA_ACTIONS is intrinsic to the Forgejo runner (=true there, unset on GitHub;
// GITHUB_ACTIONS is set on BOTH so it can't discriminate). Publish + commit-back are gated on it.
const onForgejo = !!process.env.GITEA_ACTIONS;

const noteKeywords = ['BREAKING CHANGE', 'BREAKING CHANGES', 'BREAKING'];

const branches = [
    'main',
    {
        name: 'release/*',
        prerelease: 'rc',
    },
];

const commitAnalyzerConfig = [
    '@semantic-release/commit-analyzer',
    {
        preset: 'conventionalcommits',
        releaseRules: [
            { type: 'feat', release: 'minor' },
            { type: 'feature', release: 'minor' },
            { type: 'fix', release: 'patch' },
            { type: 'bugfix', release: 'patch' },
            { type: 'hotfix', release: 'patch' },
        ],
        parserOpts: { noteKeywords },
    },
];

const releaseNotesGeneratorConfig = [
    '@semantic-release/release-notes-generator',
    {
        preset: 'conventionalcommits',
        presetConfig: {
            types: [
                { type: 'feat', section: 'Added' },
                { type: 'feature', section: 'Added' },
                { type: 'fix', section: 'Fixed' },
                { type: 'bugfix', section: 'Fixed' },
                { type: 'hotfix', section: 'Fixed' },
                { type: 'perf', section: 'Performance' },
                { type: 'refactor', section: 'Changed' },
                { type: 'docs', section: 'Docs', hidden: false },
                { type: 'test', section: 'Tests', hidden: false },
                { type: 'ci', section: 'Continuous Integration', hidden: false },
                { type: 'build', section: 'Build System', hidden: false },
                { type: 'chore', section: 'Internal', hidden: true },
            ],
        },
        parserOpts: { noteKeywords },
    },
];

const changelogConfig = [
    '@semantic-release/changelog',
    {
        changelogFile: 'CHANGELOG.md',
    },
];

const gitConfig = [
    '@semantic-release/git',
    {
        assets: ['CHANGELOG.md'],
        message: 'chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}',
    },
];

const execConfig = [
    '@semantic-release/exec',
    {
        successCmd: '[ -n "$GITHUB_OUTPUT" ] && echo "version=${nextRelease.version}" >> "$GITHUB_OUTPUT" || true',
    },
];

const branchName = process.env.GITHUB_REF_NAME || process.env.BRANCH_NAME || '';
const isMainBranch = branchName === 'main';

// Forgejo: @saithodev/semantic-release-gitea (reads GITEA_URL/GITEA_TOKEN set by the shared
// composite action). GitHub: the original @semantic-release/github, kept for the mirror.
const publishConfig = onForgejo ? ['@saithodev/semantic-release-gitea', {}] : '@semantic-release/github';

const plugins = [
    commitAnalyzerConfig,
    releaseNotesGeneratorConfig,
    ...(isMainBranch && onForgejo ? [changelogConfig, gitConfig] : []),
    execConfig,
    publishConfig,
];

export default {
    branches,
    tagFormat: '${version}',
    plugins,
};
