<?php
$this->layout('base', ['title' => 'Welcome']) ?>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>

<div id="app" class="sugo"></div>
<script>
	const app = Vue.createApp({
		data() {
			return {
				users: <?= json_encode($users) ?>,
				selectedUser: <?= $selectedUser ? "'" . str_replace("'", "\'", $selectedUser) . "'" : "null" ?>,
				selectedUserData: null,
				document: null,
				finalDocument: null,
				loading: false,
				screen: <?= $selectedUser ? "'sign'" : "'chooseUser'" ?>,
				mouseDown: false,
				toEmail: null,
			}
		},
		watch: {
			screen() {
				if (this.screen == 'sign') {
					history.pushState(null, '', '/sugo.php?uid=' + encodeURIComponent(this.selectedUser));
				} else {
					history.pushState(null, '', '/sugo.php');
				}
			}
		},
		mounted() {
			if (this.screen == 'sign') {
				this.sign();
			}
		},
		computed: {
			needsToSign() {
				return this.users.filter(user => user.needsToSign);
			},
			isBlocked() {
				return this.users.filter(user => user.isBlocked);
			},
			everyoneElse() {
				return this.users.filter(user => !user.needsToSign && !user.isBlocked);
			},
			selectedUserData() {
				return this.users.find(user => user.id == this.selectedUser);
			}
		},
		methods: {
			async sign() {
				if (!this.selectedUser || this.loading) {
					return;
				}
				this.loading = true;
				this.document = await fetch('/sir.php?uid=' + encodeURIComponent(this.selectedUser), {credentials: 'same-origin'}).then(res => {
					if (!res.ok) {
						throw new Error('An error occurred fetching the original document');
					}
					return res;
				}).then(res => res.arrayBuffer()).catch(() => {
					alert('An error occurred fetching the original document');
					this.loading = false;
				});
				this.screen = 'sign';
				this.loading = false;
			},
			handleMouseDown(e) {
				e.preventDefault();
				this.mouseDown = true;
				const ctx = this.$refs.signature.getContext('2d');
				ctx.beginPath();
				ctx.moveTo(e.offsetX, e.offsetY);
			},
			handleMouseMove(e) {
				e.preventDefault();
				if (!this.mouseDown) return;
				const ctx = this.$refs.signature.getContext('2d');
				ctx.lineWidth = 3;
				ctx.lineTo(e.offsetX, e.offsetY);
				ctx.stroke();
			},
			handleMouseUp(e) {
				e.preventDefault();
				this.mouseDown = false;
				const ctx = this.$refs.signature.getContext('2d');
			},
			handleTouchStart(e) {
				e.preventDefault();
				this.mouseDown = true;
				const ctx = this.$refs.signature.getContext('2d');
				ctx.beginPath();
				ctx.moveTo(e.touches[0].clientX - this.$refs.signature.getBoundingClientRect().left, e.touches[0].clientY - this.$refs.signature.getBoundingClientRect().top);
			},
			handleTouchMove(e) {
				e.preventDefault();
				if (!this.mouseDown) return;
				const ctx = this.$refs.signature.getContext('2d');
				ctx.lineWidth = 3;
				ctx.lineTo(e.touches[0].clientX - this.$refs.signature.getBoundingClientRect().left, e.touches[0].clientY - this.$refs.signature.getBoundingClientRect().top);
				ctx.stroke();
			},
			handleTouchEnd(e) {
				e.preventDefault();
				this.mouseDown = false;
				const ctx = this.$refs.signature.getContext('2d');
			},
			clear() {
				const ctx = this.$refs.signature.getContext('2d');
				ctx.clearRect(0, 0, 500, 250);
			},
			async generatePdf() {
				// saving this at the beginning so that we can hide the canvas
				let signatureData = this.$refs.signature.toDataURL("image/png");
				this.loading = true;

				let pdf = await PDFLib.PDFDocument.load(this.document).catch(() => {
					alert('An error occurred while loading the original document');
					this.loading = false;
				});
				let pages = pdf.getPages();
				let thirdPage = pages[2];
				// add the date
				/*let now = new Date();
				thirdPage.drawText(now.toLocaleDateString("en-GB"), {
					x: 125,
					y: 255,
					size: 12,
				});*/ // disabled because this is now added by the server
				// add the signature
				let signatureBuffer = Uint8Array.from(atob(signatureData.split(',')[1]), c => c.charCodeAt(0));
				let signatureImage = await pdf.embedPng(signatureBuffer);
				thirdPage.drawImage(signatureImage, {
					x: 360,
					y: 110,
					width: 180,
					height: 90,
				});
				this.finalDocument = await pdf.save();
				//this.toEmail = this.selectedUserData.email;
				//this.screen = 'sendEmail';
				this.download(); // temp
				this.loading = false;
			},
			send() {
				if (!this.toEmail || this.loading) {
					return;
				}
				this.loading = true;
				let formData = new FormData();
				formData.append('email', this.toEmail);
				formData.append('document', new Blob([this.finalDocument], {type: 'application/pdf'}), 'sir.pdf');
				fetch('/?page=email', {
					method: 'POST',
					body: formData,
				}).then(res => {
					if (res.status == 206) {
						this.screen = 'chooseUser';
						this.loading = false;
					} else {
						alert('An error occurred');
						this.loading = false;
					}
				});
			},
			download() {
				let pdfBlob = new Blob([this.finalDocument], {type: 'application/pdf'});
				let pdfUrl = URL.createObjectURL(pdfBlob);
				let a = document.createElement('a');
				a.href = pdfUrl;
				a.download = 'sir.pdf';
				a.click();
				URL.revokeObjectURL(pdfUrl);
				if (this.users.length == 0) {
					// just redirect to the home page
					window.location.href = '/';
				}
				this.screen = 'chooseUser';
			},
		},
		template: `
			<div>
				<h1>Sign the SIR</h1>
				<div v-if="loading">Loading...</div>
				<template v-else-if="screen == 'chooseUser'">
					<div>Select the person:</div>
					<select class="form-select" v-model="selectedUser">
						<optgroup label="Needs to sign">
							<option v-for="user in needsToSign" :value="user.id"><b>{{ user.name }}</b> ({{ user.id }})</option>
						</optgroup>
						<optgroup label="Active users">
							<option v-for="user in everyoneElse" :value="user.id"><b>{{ user.name }}</b> ({{ user.id }})</option>
						</optgroup>
						<optgroup label="Is blocked">
							<option v-for="user in isBlocked" :value="user.id"><b>{{ user.name }}</b> ({{ user.id }})</option>
						</optgroup>
					</select>
					<button @click="sign" class="btn btn-primary">Sign</button>
				</template>
				<div v-else-if="screen == 'sign'">
					<div style="display: flex; justify-content: space-between;">
						<button v-if="users.length > 0" @click="screen = 'chooseUser'" class="btn btn-outline-secondary">Back</button>
						<button @click="clear" class="btn btn-secondary">Clear</button>
						<button @click="generatePdf" class="btn btn-primary">Download</button>
					</div>
					<div style="position: absolute; width: 500px; display: flex; border: dashed 2px black; margin-top: 40px; margin-left: auto; margin-right: auto; left: 0; right: 0;">
						<div style="height: 2px; width: 100%; position: absolute; bottom: 75px; background: #bbb;"></div>
						<canvas ref="signature" width="500" height="250" style="z-index: 1000;" @mousedown="handleMouseDown" @mousemove="handleMouseMove" @mouseup="handleMouseUp" @mouseleave="handleMouseUp" @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd"></canvas>
					</div>
				</div>
				<template v-else-if="screen == 'sendEmail'">
					<div>
						<label for="email">Email:</label><br>
						<input type="email" id="email" v-model="toEmail"><br><br>
						<button @click="send">Send</button>
					</div>
					<div>
						<button @click="download">Download</button>
					</div>
				</template>
			</div>
		` // there is some unused code for sending the email, for now we only allow downloads
	});
	app.mount('#app');
</script>