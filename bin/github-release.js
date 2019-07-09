/* global require */
const Octokit = require('@octokit/rest');
const git = require('simple-git');
const fs = require('fs');
const path = require('path');


async function release() {
	// Get token from environment, should be provided by CircleCI
	const token = process.env.GITHUB_TOKEN;
	if (!token) {
		throw new Error('GITHUB_TOKEN not set');
	}

	const versionTag = process.env.CIRCLE_TAG;
	if(!versionTag){
		throw new Error('CIRCLE_TAG not set');
	}


	const releaseFile = process.env.RELEASE_FILE;
	if (!releaseFile) {
		throw new Error('RELEASE_FILE not set');
	}

	// check the release file exists
	if (!fs.existsSync(releaseFile)){
		throw new Error(`Cannot read RELEASE_FILE ${releaseFile} `);
	}
	const fileStats = fs.statSync(releaseFile);
	const releaseFileSize = fileStats['size'];
	console.info(`Will attach ${releaseFile} of size ${releaseFileSize}`);




	const repoUser = process.env.CIRCLE_PROJECT_USERNAME;
	if(!repoUser){
		throw new Error('Unable to determine repo user. Is CIRCLE_PROJECT_USERNAME env var set?')
	}

	const repoName = process.env.CIRCLE_PROJECT_REPONAME;
	if(!repoName){
		throw new Error('Unable to determine repo user. Is CIRCLE_PROJECT_REPONAME env var set?')
	}



	const octokit = new Octokit({
		auth: token
	});








	// Any release not from the master branch must be a pre-release
	const productionRelease = !!versionTag.match(/^v[0-9]*.[0-9]*.[0-9]*$/);

	let release;
	try {

		const createResponse = await octokit.repos.createRelease({
			owner: repoUser,
			repo: repoName,
			tag_name: versionTag,
			name: `Version ${ versionTag}`,
			body: `Release created by CircleCI on tag ${versionTag}`,
			prerelease:!productionRelease
		});
		if (createResponse) {
			release = createResponse.data;
			console.info(`Created release @ ${release.url}`);
		}


		await octokit.repos.uploadReleaseAsset({
			url: release.upload_url,
			headers: {
				'content-type': 'application/zip',
				'content-length': releaseFileSize
			},
			name: path.basename(releaseFile),
			file: fs.createReadStream(releaseFile)
		});
	} catch (githubErr) {
		console.warn(githubErr);
		if (release) {
			console.info(`Deleting Release ${release.url} `);
			await octokit.repos.deleteRelease({
				owner: repoUser,
				repo: repoName,
				release_id: release.id
			});


		}
		throw new Error(`GitHub API returned error response creating new release. ${githubErr.errors[0].code}`);

	}


}

async function getUserAndRepoFromGitConfig() {
	return new Promise(resolve => {
		git().raw(['config', '--get', 'remote.origin.url'], (err, result) => {
			if (err || !result) {
				resolve(null);
				return;
			}
			const tokens = result.split('/').reverse();
			resolve({
				user: tokens[1],
				repo: tokens[0].split('.')[0]
			});
		});
	});
}


release().then(value => {
	console.log('Release done');
}).catch(reason => {
	console.error('Release Failed');
	console.error(reason);
	process.exit(1);
});

